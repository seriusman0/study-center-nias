---

# Product Requirements Document (PRD): Manual One-Way Sync (DB & Storage)

## 1. Context & Architecture

* **Project Context:** Laravel application (version 10/11) deployed directly on OS / Bare-Metal / VM (**STRICTLY NON-DOCKER**).
* **Repository:** Both web instances share the exact same GitHub repository and codebase. Behavior is determined strictly by `.env` variables.
* **Web 1 (Source):** `studycenter.overcomer.my.id` (Acts as the data provider).
* **Web 2 (Target):** `studycenter.seriusman.shop` (Acts as the data consumer/requester).
* **Feature Goal:** A button on the Target Admin Dashboard to manually pull and merge (upsert) data and storage files from the Source Web.

## 2. Technical Constraints & Safety Rules

* **DO NOT** modify existing database migrations or core schemas. Use existing unique keys (like `email`, `slug`, or `id`) for the `upsert` process.
* **DO NOT** modify existing authentication flows. The sync API must use a dedicated custom header (`X-Sync-Key`) validated against a `.env` variable.
* **DO NOT** execute the sync synchronously in the Controller if it risks a timeout. Move the DB fetch and `rsync` logic into a **Laravel queued Job**, and let the controller just dispatch the job and return a success message.
* **Storage Sync:** Must use OS-level `rsync` executed via Laravel's `Process` facade. Do not write pure PHP file-copying loops.

## 3. Environment Configuration

Add the following variables. The codebase must handle cases where these are null gracefully.

```env
# Required for authentication between the two instances
SYNC_SECRET_KEY=your_secure_random_string_here

# The URL of the other instance. 
# On Web 2, this is: https://studycenter.overcomer.my.id
# On Web 1, this can be left blank or point to Web 2.
SYNC_TARGET_URL=

```

## 4. Feature Specifications

### A. API Provider (Data Export Endpoint)

* **File:** `routes/api.php` and `app/Http/Controllers/Api/SyncProviderController.php`
* **Route:** `GET /api/sync/export`
* **Security Middleware/Logic:** Must check if `$request->header('X-Sync-Key') === env('SYNC_SECRET_KEY')`. Return `401 Unauthorized` if it fails.
* **Response:** Return a JSON payload containing arrays of models to be synced.
* *AI Instruction:* Please prepare the payload to export models such as `User`, `Category`, `Course` (adjust actual models based on the codebase context).



### B. Admin UI (Trigger Button)

* **View File:** Determine the main admin dashboard view (e.g., `resources/views/admin/dashboard.blade.php`).
* **Element:** Add a distinct, visually separate card/section titled "System Synchronization".
* **Action:** A form containing a button "Pull Data from Source Web". Method `POST` targeting `admin.sync.pull`. Includes `@csrf`.

### C. Sync Consumer (Data Import & Storage Merge)

* **File:** `routes/web.php` (inside admin middleware group) and `app/Http/Controllers/Admin/SyncConsumerController.php`.
* **Route:** `POST /admin/sync/pull` (Name: `admin.sync.pull`).
* **Logic (Controller):**
1. Check if `SYNC_TARGET_URL` is set. If not, return back with an error flash message.
2. Dispatch a Job (e.g., `DispatchSyncJob::dispatch()`).
3. Return a redirect back with a success flash message: "Sync process has been queued and is running in the background."



### D. The Sync Job (Background Worker)

* **File:** `app/Jobs/ProcessWebSync.php`
* **Implements:** `ShouldQueue`.
* **Database Sync Logic:**
1. Use Laravel HTTP Client (`Http::withHeaders()`) to fetch `SYNC_TARGET_URL . '/api/sync/export'`. Set a long timeout (e.g., 120 seconds).
2. Validate response. If successful, decode JSON.
3. For each model array in the JSON, use Eloquent's `upsert()`.
* *Example:* `User::upsert($data['users'], ['email'], ['name', 'password', 'updated_at']);`
* *Rule:* Always upsert parent tables before child tables to avoid Foreign Key constraint violations.




* **Storage Sync Logic:**
1. Define storage path: `$localPath = storage_path('app/public/');`
2. Construct command: `rsync -avz --update user@source_ip:/path/to/source/storage/app/public/ {$localPath}`. *(AI Instruction: Make the `user@source_ip:/path` configurable via a new `.env` variable `SYNC_RSYNC_SOURCE` to avoid hardcoding).*
3. Execute using `Illuminate\Support\Facades\Process`. Log any errors if the process fails.



## 5. Implementation Steps for AI

1. **Step 1:** Define the `.env` variables in `config/services.php` or create a new config file `config/sync.php` to map the `.env` variables cleanly.
2. **Step 2:** Create the API Provider Controller and register the `api.php` route.
3. **Step 3:** Create the Background Job (`ProcessWebSync`) containing the HTTP fetch, `upsert` logic, and `rsync` logic.
4. **Step 4:** Create the Admin Consumer Controller to dispatch the job.
5. **Step 5:** Register the `web.php` route for the admin button.
6. **Step 6:** Inject the UI button into the admin dashboard blade template.

---

