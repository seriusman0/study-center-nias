<?php

namespace App\Services;

use App\Models\CertificateTemplate;
use App\Models\IssuedCertificate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Sanitize raw HTML/CSS from admin editor.
     */
    public function sanitizeTemplate(string $rawHtml): string
    {
        $styleBlock = '';
        $rawHtml = preg_replace_callback(
            '/<style[^>]*>(.*?)<\/style>/si',
            function ($matches) use (&$styleBlock) {
                $css = $matches[1];
                $css = preg_replace('/expression\s*\(.*?\)/i', '', $css);
                $css = preg_replace('/url\s*\(\s*["\']?\s*javascript:/i', 'url(', $css);
                $css = preg_replace('/behavior\s*:/i', 'x-behavior:', $css);
                $styleBlock .= '<style>' . $css . '</style>' . "\n";
                return '';
            },
            $rawHtml
        );

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed',
            'div,span,section,article,header,footer,main,aside,' .
            'p,br,hr,h1,h2,h3,h4,h5,h6,strong,b,em,i,u,s,small,sub,sup,pre,code,' .
            'ul,ol,li,' .
            'table,thead,tbody,tfoot,tr,th,td,caption,colgroup,col,' .
            'img[src|alt|width|height],' .
            'figure,figcaption'
        );
        $config->set('HTML.AllowedAttributes',
            '*.class,*.id,*.style,' .
            'img.src,img.alt,img.width,img.height,' .
            'table.border,table.cellpadding,table.cellspacing,' .
            'td.colspan,td.rowspan,th.colspan,th.rowspan'
        );
        $config->set('CSS.AllowedProperties',
            'color,background-color,background-image,background-size,background-position,background-repeat,' .
            'font-family,font-size,font-weight,font-style,font-variant,' .
            'text-align,text-decoration,text-transform,letter-spacing,line-height,' .
            'width,height,min-width,min-height,max-width,max-height,' .
            'margin,margin-top,margin-right,margin-bottom,margin-left,' .
            'padding,padding-top,padding-right,padding-bottom,padding-left,' .
            'border,border-top,border-right,border-bottom,border-left,border-radius,border-color,border-style,border-width,' .
            'position,top,right,bottom,left,' .
            'display,flex,flex-direction,flex-wrap,align-items,justify-content,gap,flex-shrink,flex-grow,' .
            'overflow,opacity,z-index,box-shadow,' .
            'page-break-before,page-break-after,page-break-inside,' .
            'object-fit,object-position'
        );
        $config->set('URI.AllowedSchemes', ['data' => true, 'https' => true, 'http' => true]);
        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty', false);
        $config->set('Core.EscapeNonASCIICharacters', false);
        $config->set('Cache.DefinitionImpl', null);

        $purifier = new HTMLPurifier($config);
        $cleanBody = $purifier->purify($rawHtml);

        return $styleBlock . $cleanBody;
    }

    /**
     * Replace {{placeholder}} tokens with real data.
     */
    public function interpolate(
        string $htmlTemplate,
        User $student,
        string $namaCourse,
        Carbon $tanggalLulus,
        string $nomorSertifikat,
        string $logoBase64 = '',
        string $fotoBase64 = '',
    ): string {
        $cabangNama = optional($student->cabang)->nama ?? 'Pusat';

        $search = [
            '{{nama_peserta}}',
            '{{nama_kursus}}',
            '{{tanggal_lulus}}',
            '{{nomor_sertifikat}}',
            '{{cabang}}',
            '{{tahun}}',
            '{{logo}}',
            '{{foto_peserta}}',
        ];

        $replace = [
            e($student->name),
            e($namaCourse),
            $tanggalLulus->locale('id')->isoFormat('D MMMM Y'),
            e($nomorSertifikat),
            e($cabangNama),
            $tanggalLulus->year,
            $logoBase64,   // raw data URI — no e()
            $fotoBase64,   // raw data URI — no e()
        ];

        return str_replace($search, $replace, $htmlTemplate);
    }

    /**
     * Render compiled HTML to PDF binary via DomPDF.
     */
    public function renderPdf(
        string $compiledHtml,
        string $orientation = 'portrait',
        string $paperSize = 'a4',
    ): string {
        $pdf = Pdf::loadView('pdf.certificate', [
            'compiledHtml' => $compiledHtml,
            'orientation'  => $orientation,
        ])
        ->setOptions([
            'enable_remote'        => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => false,
            'dpi'                  => 150,
        ])
        ->setPaper($paperSize, $orientation);

        return $pdf->output();
    }

    /**
     * Generate unique certificate number: SCN-{YEAR}-{CABANG}-{ULID}
     */
    public function generateNomorSertifikat(User $student): string
    {
        $year = now()->year;
        $cabangCode = $student->cabang
            ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $student->cabang->nama), 0, 3))
            : 'GBL';
        if (empty($cabangCode)) {
            $cabangCode = 'GBL';
        }

        return "SCN-{$year}-{$cabangCode}-" . strtoupper(Str::ulid());
    }

    /**
     * Full issuance: interpolate → render → store file → create DB record.
     */
    public function issue(
        CertificateTemplate $template,
        User $student,
        string $namaCourse,
        Carbon $tanggalLulus,
        User $issuedBy,
    ): IssuedCertificate {
        $student->load('cabang');

        $nomor      = $this->generateNomorSertifikat($student);
        $logoBase64 = $this->logoToBase64($template->logo_path);
        $fotoBase64 = $this->avatarToBase64($student->avatar);

        $compiledHtml = $this->interpolate(
            $template->html_content,
            $student,
            $namaCourse,
            $tanggalLulus,
            $nomor,
            $logoBase64,
            $fotoBase64,
        );

        $pdfBinary = $this->renderPdf($compiledHtml, $template->orientation, $template->paper_size);

        $filePath = "certificates/{$student->id}/{$nomor}.pdf";
        Storage::disk('public')->put($filePath, $pdfBinary);

        return IssuedCertificate::create([
            'nomor_sertifikat' => $nomor,
            'user_id'          => $student->id,
            'template_id'      => $template->id,
            'issued_by'        => $issuedBy->id,
            'tanggal_lulus'    => $tanggalLulus->toDateString(),
            'nama_kursus'      => $namaCourse,
            'file_path'        => $filePath,
            'issued_at'        => now(),
        ]);
    }

    /**
     * Convert logo file to base64 data URI.
     * Falls back to default logo, then transparent 1×1 gif.
     */
    public function logoToBase64(?string $logoPath): string
    {
        $filePath = $logoPath
            ? storage_path('app/public/' . $logoPath)
            : public_path('assets/img/logo.png');

        if (file_exists($filePath)) {
            $ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = match($ext) {
                'svg'  => 'image/svg+xml',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                default => 'image/jpeg',
            };
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));
        }

        // 1×1 transparent gif
        return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    }

    /**
     * Convert student avatar to base64 data URI.
     * Returns empty string if no avatar.
     */
    public function avatarToBase64(?string $avatarPath): string
    {
        if (! $avatarPath) {
            return '';
        }

        if (Storage::disk('public')->exists($avatarPath)) {
            $filePath = storage_path('app/public/' . $avatarPath);
            $ext      = strtolower(pathinfo($avatarPath, PATHINFO_EXTENSION)) ?: 'jpeg';
            $mime     = match($ext) {
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));
        }

        return '';
    }
}
