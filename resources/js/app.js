import Alpine from 'alpinejs';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.TiptapEditor = Editor;
window.StarterKit = StarterKit;
window.Chart = Chart;

Alpine.start();
