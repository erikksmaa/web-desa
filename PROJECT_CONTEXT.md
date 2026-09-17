# Website Informasi Desa — Project Context

## Phase and inspection record

Phase 0 completed on 2026-09-17. This is the central development contract for later phases. Implement only the scope explicitly authorized by each phase; do not proceed automatically to another phase.

The project directory was completely empty, including hidden files. Classification: **A — empty/new project**. There was no Git repository, Laravel installation, dependency manifest or lockfile, environment configuration, authentication, route, migration, frontend tooling, prototype, or asset. No ancestor AGENTS.md was found in the checked path from C:\ to the project parent. No existing code requires adaptation.

Detected CLI environment:

| Tool | Observed version / result |
| --- | --- |
| PHP | 8.4.15 NTS, Windows x64 |
| Composer | 2.10.2 |
| Node | 24.18.0 |
| npm | 11.16.0 |
| MySQL client | 8.0.30; server version, connection, credentials and database not verified |
| PHP extensions | Laravel core requirements available, including PDO, pdo_mysql, mbstring, openssl, tokenizer, XML, DOM, ctype, curl, fileinfo, filter, hash, session; GD and EXIF also available |

Artisan inspection, composer show, npm list, route listing and migration status are not applicable without an application or manifests. No packages were installed, database connection attempted, migrations run, or application initialized. The web-server PHP configuration has not been verified.

## Purpose and scope

A public Indonesian village information website with a lightweight admin publishing CMS. Citizens access content without accounts. The approved static prototype, when supplied, is a reference for structure, hierarchy, flow and behavior, not a visual specification to copy. Do not invent findings about unavailable prototype files.

Public modules: homepage, village profile, SOTK, village officials, news, announcements, agenda/calendar, public documents/downloads, gallery, and general village information.

Excluded everywhere, including routes, navigation, tables and packages: Potensi Desa, Pengaduan Masyarakat/complaint tracking, UMKM, Produk UMKM, marketplace, e-commerce, payments, checkout, public registration, citizen login and community accounts.

## Technical baseline for subsequent phases

| Area | Decision |
| --- | --- |
| Backend | Laravel 13.x, current compatible patch resolved and locked during Phase 1 |
| PHP | Minimum 8.3; use existing PHP 8.4 locally |
| Composer | Composer 2.x; keep current installation |
| Rendering | Server-rendered Blade |
| CSS | Bootstrap 5.3.8 baseline, installed through npm in a later phase; centralized project CSS tokens |
| JavaScript | Vanilla JavaScript and Bootstrap interactions only as needed |
| Build | Laravel Vite plugin with compatible Vite versions supplied/resolved for Laravel 13; Blade @vite entry points; commit lockfiles |
| Node | Project baseline Node 24.x; current 24.18.0 satisfies documented Vite requirements; check resolved package engines in Phase 1 |
| Database | MySQL 8.0+ project compatibility floor, InnoDB and utf8mb4; prefer maintained MySQL 8.4 for a newly provisioned server, subject to hosting confirmation |
| Authentication | Native Laravel web/session guard; users are CMS administrators; no framework-based UI starter kit |
| Storage | Laravel Storage; public disk for intentionally public assets; private disk for unpublished/restricted files |

Do not introduce React, Vue, SPA, Inertia, Livewire, Filament, RBAC packages, repository layers, external search engines or analytics infrastructure. FullCalendar is optional in the agenda phase only. No dependency upgrades or additions were made in Phase 0. Exact Vite/plugin versions are deliberately deferred until dependency resolution, rather than inventing an untested combination.

Official compatibility references checked during Phase 0:

- [Laravel 13 releases and PHP minimum](https://laravel.com/framework/docs/releases)
- [Laravel PHP extension requirements](https://laravel.com/docs/13.x/deployment)
- [Laravel database support](https://laravel.com/docs/13.x/database)
- [Laravel Vite integration](https://laravel.com/docs/13.x/vite)
- [Vite Node requirements](https://vite.dev/guide/)
- [Bootstrap 5.3.8](https://getbootstrap.com/docs/5.3/getting-started/introduction/)

## Information architecture

Main navigation: Beranda; Profil Desa (Visi & Misi, Sejarah Desa, SOTK, Perangkat Desa); Berita; Pengumuman; Agenda; Berkas / Unduhan; Galeri. Keep nesting shallow. General village information belongs within profile/home/footer, without another unnecessary module.

Homepage section order: hero/banner slider; village identity; village head welcome; latest news; latest announcements; upcoming agenda; latest public documents; gallery preview; contact information; footer. Future sliders must support keyboard operation and accessible pause controls if auto-rotation is introduced.

Admin module groups:

- Dashboard.
- Content: news, news categories, announcements.
- Agenda: agenda CRUD.
- Documents: categories and public documents.
- Gallery: albums and photos.
- Village profile: information, vision, mission, history, head welcome/profile, organizational structure and officials.
- Appearance: hero banners.
- System: admin users and settings.

Dynamic content: news, announcements, agendas, documents, gallery, banners, officials and head welcome. Semi-dynamic content: identity, logo, vision, mission, history, contact/address, social links, head profile, SOTK image and footer. Both must eventually be admin-editable. Only layout, navigation structure, design system and reusable structural sections are static. Do not hardcode editable business content.

## Laravel architecture and naming

Use Route -> Controller -> Model -> Blade, adding a service only for meaningful shared/nontrivial behavior. Prefer Eloquent relationships, route model binding, resource controllers and Form Requests where useful. No trivial CRUD service wrappers or unnecessary interfaces.

Planned structure (not created in Phase 0):

```text
app/Http/Controllers/Public/    # public rendering controllers
app/Http/Controllers/Admin/     # CMS and session controllers
app/Http/Requests/              # validation as modules need it
app/Models/
resources/views/layouts/{public,admin}.blade.php
resources/views/public/{home,profile,news,announcements,agenda,documents,gallery}/
resources/views/admin/{auth,dashboard,news,news-categories,announcements,agendas,document-categories,documents,gallery,village-profile,officials,banners,users,settings}/
resources/views/components/
resources/views/partials/
resources/css/app.css
resources/js/app.js
routes/web.php
```

Create folders only when needed. Classes PascalCase; methods camelCase; tables/columns snake_case; Blade names lowercase with kebab-case for multiple words. Code/module names English; public paths and UI Indonesian.

Public route plan: /, /profil, /profil/visi-misi, /profil/sejarah, /profil/sotk, /profil/perangkat-desa, /berita, /berita/{slug}, /pengumuman, /pengumuman/{slug}, /agenda, /berkas, /galeri. Detail/download routes beyond these are finalized in their module phase. Use public.* names and admin.* names consistently.

Admin route group: /admin prefix and admin. name prefix. /admin/login is guest-only; /admin/dashboard and every CMS action require authenticated admin access. /admin redirects according to session state. Use /admin/news, /admin/announcements, /admin/agendas, /admin/documents, /admin/gallery and conventional paths for other modules when authorized. Logout is a CSRF-protected POST.

Use one session guard and users table; every authenticated user is an administrator. No roles or permissions tables. Only add is_active if disabling administrators is required; enforce it across admin requests, including existing sessions. Use hashed passwords, login throttling, session regeneration after login and session invalidation/CSRF-token regeneration after logout. Decide initial admin provisioning in the authentication phase; never commit default credentials. No public registration, social login, citizen dashboard or email verification workflow.

## Data architecture review

Retain the proposed **12 business entities**; no additional business table is currently justified:

| Entity | Intended responsibility / relationship |
| --- | --- |
| users | Administrators only |
| news_categories | One category has many news items |
| news | Belongs to a category; title, unique slug and publication metadata |
| announcements | Independent publishing items; optional attachment only if required |
| agendas | Start/end times and event information |
| document_categories | One category has many documents |
| documents | Category, file metadata/reference, publication state; optional download_count |
| gallery_albums | Album metadata and visibility |
| gallery_photos | Belongs to album; original path, optional thumbnail path, alt/caption and display order |
| village_officials | Official profiles and display order |
| banners | Image, alt text, optional validated link and display order |
| settings | Allowlisted site configuration and singleton profile content |

Key-value settings are appropriate at this scale: unique key plus text value, with explicit application-defined validation/casting for each allowed key. JSON encoding only for naturally structured values such as social links. Use a fixed admin form, not an arbitrary user-defined configuration editor. No credentials in settings. Keys include village_name, logo, address, phone, email, social_links, vision, mission, history, welcome_message, head_name, head_photo, organization_structure and footer_information. A dynamic welcome message does not require its own table when there is only one editable current value.

Head profile settings are the source for the welcome section; officials are the source for the staff directory. Avoid two independently editable copies of the same head identity if the head also appears in that directory: settle a reuse/reference rule in the profile phase. SOTK remains an image plus accessible descriptive text; no organizational tree table is currently needed.

Framework infrastructure tables, such as sessions/cache/jobs, are not business entities. Inspect the Laravel scaffold and choose drivers intentionally in Phase 1; database sessions/queues are not mandatory. Do not add infrastructure merely to increase scope.

Future schema rules:

- Publishing content uses draft/published; display switches for banners/officials use active/inactive. No multi-step workflow. For dated publishing, public visibility requires published and published_at <= now; reuse this rule for lists, detail pages, search and downloads. Gallery photo visibility also respects its album.
- Unique readable slugs derived from titles, suffix collisions deterministically, enforce database uniqueness and handle concurrent collisions. Preserve existing slugs on ordinary title edits by default. Finalize URL changes explicitly.
- Index slugs uniquely, foreign keys and justified status/date queries; avoid redundant indexes. Eager-load list relationships and paginate news, announcements, documents and albums server-side.
- Keyword/category filters for news/documents; keyword/status for admin lists. No external search service.
- Validate agenda end >= start. Confirm the village timezone before implementation; the development machine timezone is not proof of the village timezone. Use a consistent storage/display convention.
- No universal SoftDeletes. Default to explicit permanent deletion unless recovery is required by the relevant phase. Referenced categories cannot be deleted until items are reassigned. Album deletion requires explicit handling of child photos and their files.
- Database transactions do not roll back filesystem changes. Validate/store replacements before switching references; remove superseded/deleted files after database success and handle cleanup failures intentionally. Do not silently cascade away file references without cleanup.
- Simple integer news views/document download_count only if required; not unique-user analytics. A public download endpoint checks visibility, resolves a trusted Storage reference and increments its counter before returning the file.

This is a conceptual review, not a final column specification or migration authorization.

## File handling and security

Use storage/app/public and a later php artisan storage:link for intentionally public files. Logical directories: banners/, news/, announcements/, documents/, gallery/, officials/, settings/. Store relative paths and needed metadata, never binaries or absolute private filesystem paths in database content.

Files on the public disk can be accessed directly: hiding the parent record cannot make those files confidential, and direct links bypass download counters. Use private Storage plus a controlled endpoint where draft confidentiality or mandatory download checks/counting matter. Do not build a document permission system; this only enforces publishing visibility.

Validate extension, detected MIME, size and image decoding/type; use generated safe filenames. Do not allow executable uploads or unsanitized SVG by default. Document formats (potential PDF, DOC, DOCX, XLS, XLSX) and per-type size limits are decided in the documents phase. Validate remote link schemes. Never trust client MIME, filenames, IDs or paths.

Use wide landscape hero images, consistent 16:9 news previews, portrait officials and consistent cropped gallery thumbnails with originals retained. Use object-fit appropriately rather than distortion. Optimize assets during upload only when the responsible phase implements it.

CSRF, escaped Blade output, validated/allowlisted input and safe mass assignment are mandatory. Authentication and resource access checks apply to all CMS actions. Rich text, if later required, needs server-side sanitization before any raw HTML rendering; default to escaped text. Public errors must not leak stack traces, SQL, filesystem paths or secrets. Production debug must be disabled. Secrets belong only in .env; Phase 1 must create/verify .gitignore excludes .env and keep only safe placeholders in .env.example.

## UI, accessibility and SEO

Modern, restrained official village website: generous whitespace, readable sans-serif typography, limited green primary palette, white/gray neutrals and restrained accent. Centralize color, typography, spacing and focus tokens in the project stylesheet; finalize exact values later. One main font family and consistent title/section/card/body/metadata/caption hierarchy are sufficient.

No excessive gradients, glassmorphism, decorative animations, complex layouts or startup/shop styling. Desktop, tablet and mobile are core requirements for public and admin pages. Semantic headings, associated labels, meaningful alt text, keyboard controls, visible focus and readable contrast are mandatory. Use a 4.5:1 normal-text contrast target and respect reduced-motion preferences.

Public reusable pieces: navbar, footer, section heading, news/announcement/agenda cards, document item, gallery card, pagination, breadcrumb and empty state. Admin pieces: sidebar, top navigation, page header, breadcrumb, form fields, status badges, tables, pagination, alerts, confirmation modal, empty state, image preview and upload input. Extract shared markup where it reduces actual duplication. Configure Laravel pagination for Bootstrap when implemented.

Basic SEO only: meaningful page title, meta description, semantic headings, readable slugs and useful Open Graph data. No advanced SEO subsystem. JavaScript is limited to needed navbar, preview, confirmation, lightbox and calendar interactions.

## Development discipline and next-phase gate

- Read this document before each phase; keep it current when authorized decisions change.
- Implement only the assigned phase. Small necessary supporting changes must be explained in the Completion Report. Do not build unrelated modules or redesign unrelated pages.
- Keep methods small and names clear; use Laravel formatting conventions and comments only when they add information. No unexplained magic values or premature optimization.
- Preserve working files and user changes. Do not delete apparently unused material without understanding its purpose. Never run destructive database resets or seeds without explicit authorization.
- Verify relevant behavior for each actual change. Future authentication/publication/upload work needs access-control, validation and visibility checks; future UI work needs mobile and keyboard verification.
- Report changes, dependencies, database and route effects, verification, issues, risks and deviations explicitly.

Phase 0 changed documentation only. No prototype-specific layout, hierarchy, asset reuse or obsolete-section findings can be established until the prototype is provided. Its absence does not prevent foundation work, but must be resolved before claiming prototype parity.

Ready for externally reviewed **Phase 1 — Project Initialization & Foundation**. Preserve these documents during scaffolding into this now nonempty directory; do not overwrite them or run project creation blindly. Phase 1 should verify hosting PHP/MySQL compatibility, create the minimal Laravel foundation and Git ignore rules, resolve compatible locked dependencies, and validate the app/build/database connection within its explicit scope. No CMS implementation is authorized by this document alone.

## Phase 1 foundation update — 2026-09-17

This update records the implemented foundation and supersedes conflicting Phase 0 proposals without removing the original record.

- The Laravel 13.0.0 skeleton was downloaded using Composer into .phase1-scaffold with installation/scripts disabled, then merged into the root only after collision checks. The scaffold README was excluded; both original documents were preserved. The temporary scaffold was removed after its files were moved.
- Resolved Laravel framework: 13.32.0. Composer uses stable releases and config.platform.php=8.3.0 to preserve PHP 8.3 compatibility on the PHP 8.4 development machine.
- Official database baseline is **MySQL 8.0+**; MySQL 8.4 is not required. Local MySQL server 8.0.30 and connection to web_desa were verified. Only the three default Laravel migrations ran; no business tables were added. Default password_reset_tokens/cache/job infrastructure tables are retained without enabling password-reset or queue workflows.
- APP_NAME is Website Desa, timezone Asia/Jakarta, locale id, fallback en. These are environment-backed. Debug defaults to false. Actual connection credentials and generated app key stay in ignored .env.
- Public home uses HomeController and route name home (superseding the earlier public.* naming proposal for this route). Admin controllers live under Admin/, with Auth/AuthController and a LoginRequest under Http/Requests/Admin/.
- Required routes only: GET /; GET/POST /admin/login; POST /admin/logout; GET /admin/dashboard. The default /up health route remains. No /admin redirect or future module routes were added. Automatic local Storage temporary download/upload routes are disabled; future publication-aware file endpoints need explicit implementation.
- One native session/web guard; authenticated users are administrators. Login validates email/password, throttles five failed attempts per normalized-email/IP pair for 60 seconds, regenerates the session, and redirects to dashboard. Logout invalidates the session and regenerates the CSRF token. Middleware redirects guests to admin.login and authenticated users away from login.
- AdminUserSeeder is an explicit Phase 1 exception to the earlier prohibition on committed default credentials: it contains only the user-requested public demonstration account, runs in local environments only, hashes the password, and preserves existing accounts. Never use demonstration credentials in production.
- Database sessions, file cache and synchronous queues keep the runtime simple. No additional authentication packages or frontend frameworks were installed.
- Bootstrap 5.3.8 and Popper 2.11.8 are npm-managed. Vite 7.3.6 with laravel-vite-plugin 2.1.0 bundles resources/css/app.css and resources/js/app.js. Default Tailwind/Axios/concurrently references were removed before npm installation. No Sass or CDN dependencies.
- Public and admin Blade layouts and partials are in place, along with a standalone login page, placeholder homepage and minimal dashboard. Future public navigation labels are non-clickable. The admin sidebar links only dashboard and the public website.
- Default welcome view, unused Axios bootstrap script and example tests were replaced by scoped foundation implementation/tests. Unsafe convenience setup scripts that automatically migrate and the unnecessary multi-process dev script were omitted; README documents explicit setup steps.
- Feature tests force SQLite :memory: and check the resolved database before RefreshDatabase can run. Tests cover public access, protected access, invalid/valid login, session/CSRF rotation on logout, throttling, missing public-account routes and seeder safeguards. No test uses the development MySQL database.
- Git initialized; no commit created. Standard ignores plus .env.* protection retain .env.example. A local, exact-path Git safe.directory exception handles the sandbox-created metadata ownership; no wildcard exception was added.
- Real local HTTP checks confirmed homepage/login 200, guest redirect, seeded admin login/dashboard, logout protection, missing-CSRF 419, and built asset delivery. Browser automation failed to start twice, so responsive visual verification remains pending.
- Phase 1 stops here. Phase 2 requires external review and explicit instructions; no business models, migrations or CRUD are authorized by this update.


## Phase 2 database architecture — 2026-09-17

Phase 1 was reviewed and approved. Phase 2 implements schema, models, factories, development seeders and schema tests only. This section finalizes and supersedes the earlier provisional entity/settings/soft-delete proposals.

### Implemented entities

All business tables use conventional bigint IDs and timestamps. MySQL uses InnoDB/utf8mb4_unicode_ci. Migration definitions also run on SQLite.

| Table | Main fields beyond ID/timestamps |
| --- | --- |
| users | Existing name, email, password, email_verified_at, remember_token retained unchanged |
| news_categories | name, unique slug, description, is_active, sort_order |
| news | required news_category_id, nullable user_id, title, unique slug, excerpt, content, thumbnail, status, published_at, views, deleted_at |
| announcements | nullable user_id, title, unique slug, content, attachment_path, attachment_original_name, status, published_at, expires_at, deleted_at |
| agendas | nullable user_id, title, unique slug, description, location, start_at, end_at, status, deleted_at |
| document_categories | name, unique slug, description, is_active, sort_order |
| documents | required document_category_id, nullable user_id, title, description, file_path, original_filename, mime_type, file_size, status, published_at, download_count, deleted_at |
| gallery_albums | nullable user_id, title, unique slug, description, cover_image, event_date, status, sort_order |
| gallery_photos | required gallery_album_id, image_path, caption, alt_text, sort_order |
| village_officials | name, position, photo_path, biography, is_village_head, is_active, sort_order |
| banners | title, subtitle, image_path, cta_label, cta_url, is_active, sort_order, starts_at, ends_at |
| settings | unique key, nullable longText value, type (default string), group (default general) |

Names/positions are bounded at 150 characters, slugs at 190, ordinary titles at 255, storage paths at 512 and CTA URLs at 2048. Counters/file sizes are unsigned big integers; ordering fields are unsigned integers. Optional content/file metadata is nullable. Files are references only; no binary data is stored.

### Relationships and lifecycle

- User hasMany news(), announcements(), agendas(), documents(), galleryAlbums(). Each corresponding content model uses author() with user_id consistently, including the document uploader.
- NewsCategory hasMany news(); News belongsTo category(). DocumentCategory hasMany documents(); Document belongsTo category().
- GalleryAlbum hasMany photos(); GalleryPhoto belongsTo album().
- Category foreign keys are required and RESTRICT deletion. Trashed news/documents still reference their categories: reassign or permanently remove those records before deleting a category.
- Authored-content foreign keys are nullable with SET NULL on user deletion. Deleting an administrator never cascades into public content.
- Album deletion CASCADEs photo database rows only. It does not remove physical files or fire Eloquent deletion events for cascaded photos. Future deletion logic must collect paths and explicitly clean up files.
- FK updates use the database NO ACTION behavior; IDs should not be rewritten.
- SoftDeletes applies only to News, Announcement, Agenda and Document. Other business models use hard deletion; User remains unchanged.
- Unique slugs remain reserved while content is trashed so restoration cannot collide. Future slug validation/generation must include trashed records.
- No is_active field was added to users: it is unnecessary for this phase and would require additional authentication/session behavior. No roles or permissions were introduced.

### Status, casts, scopes and indexes

News, Announcement, Agenda, Document and GalleryAlbum define STATUS_DRAFT / STATUS_PUBLISHED. Their status columns are varchar(20), default draft, never database ENUM. Categories, officials and banners use boolean is_active; photos inherit album publication rather than carrying another status.

Business models use explicit fillable lists; IDs, timestamps and deleted_at are not mass assignable. Future controllers must still validate/allowlist request fields and set trusted author IDs/counters server-side. Fillable is not authorization.

Datetime/date, boolean and counter/order/file-size casts are implemented using casts(). No automatic counter increments, slug generation, file hooks or observer logic exists.

Small scopes:
- News::published() and Document::published(): published status and published_at <= now().
- Announcement::published(): the same plus no expiry or expires_at > now(). An expiry exactly at now is expired.
- Agenda::published() and GalleryAlbum::published(): status only. Event dates are not publication dates.
- Category, VillageOfficial and Banner active() scopes filter only is_active. Banner active() does not implement scheduling.
- Soft-deleted items are excluded by Eloquent unless explicitly requested with withTrashed().

Query scopes are covered with frozen-time tests. Date handling follows the existing Asia/Jakarta application configuration; event start/end values must be entered consistently. Future forms validate end_at >= start_at and schedule boundaries.

Unique indexes cover category/content slugs and setting keys. Foreign keys, publishing status/dates, announcement expiry, agenda start date, album event date, and settings group are indexed. Photos use (gallery_album_id, sort_order); banners use (is_active, sort_order) plus starts_at. No redundant standalone index duplicates those composite prefixes. Extra indexes on tiny category flags, isolated ordering fields, agenda end_at or banner ends_at are deferred until actual queries justify them.

### Village head and settings source of truth

village_officials is the sole source of staff/head name, position and photo. Select the homepage head with is_village_head=true AND is_active=true. Do not store head_name, head_photo or head_position in settings. No boolean unique constraint exists; future application validation must enforce one active head without preventing many non-head rows.

Settings contain configuration, never secrets. The 16 approved keys are:
site.name, site.tagline, site.logo; village.address, village.phone, village.email, village.vision, village.mission, village.history, village.head_welcome; social.facebook, social.instagram, social.youtube; map.embed_url; footer.description; sotk.image.

Setting defines string/text/integer/boolean/json type constants. Values remain raw text or null; a future fixed settings form must validate each known key/type before interpreting values. No generic dynamic settings editor, automatic JSON decoder, secret storage or extra configuration tables were introduced. Welcome text belongs in settings; the head identity belongs in village_officials.

### Development data and verification

Run php artisan db:seed in APP_ENV=local to execute dedicated seeders in dependency-safe order. Root and individual business seeders refuse non-local environments. The existing AdminUserSeeder retains its safety behavior. Root seeding uses a database transaction. firstOrCreate preserves existing values and credentials; withTrashed avoids duplicating or restoring deleted demo publishing content. Run the root seeder for the complete dataset; individual content seeders depend on their categories/albums already existing.

Fresh sample counts: users 1, news categories 4, news 8, announcements 4, agendas 5, document categories 3, documents 6, albums 3, photos 12, officials 6, banners 2, settings 16. Names/content are explicitly fictional. One active head is seeded on a fresh database; an existing active head is preserved instead of adding another. Seeders do not repair later manual edits.

Nullable images remain null. Required document/photo/banner paths use development/placeholders/ references with no physical files. Documents and albums are draft; banners are inactive. Replace paths with real validated uploads before publication. No actual phone/address/social contact data is invented.

Eleven business migrations applied normally to web_desa on MySQL 8.0.30. Eight existing infrastructure tables remain: migrations, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs. The users table plus eleven new business tables yield 20 total tables; no unexpected tables were found.

The isolated SQLite suite passes 57 tests / 258 assertions, including all Phase 1 tests. New tests cover relationships, orphan rejection, restrict/null/cascade rules, unique slugs/setting keys, casts/defaults, publication boundaries, soft deletion/restoration and safe/idempotent seeders. MySQL schema/index/FK inspection and all requested model:show checks succeeded.

No controllers, routes, UI, authentication flow, uploads or dependencies changed in Phase 2. Phase 3 — Admin Foundation & Shared CMS Components requires external review and explicit authorization.


## Phase 3 admin foundation — 2026-09-17

The admin foundation is now the shared UI contract for future CMS modules. It adds no routes, migrations, CRUD controllers, uploads, or dependencies.

### Admin shell and dashboard

The authenticated admin layout uses one responsive shell: a persistent 17rem sidebar from the lg breakpoint and the same sidebar as a Bootstrap offcanvas below lg. The sticky topbar contains the mobile menu control, page context, authenticated administrator identity, and the existing CSRF-protected POST logout. The sidebar hierarchy is Utama, Konten, Informasi, Profil Desa, Tampilan, and Sistem. Only Dashboard is a link; future modules are non-interactive spans with aria-disabled and never use hash links. Active links use the reusable sidebar-link component and request()->routeIs() patterns.

DashboardController uses Eloquent counts for News, Announcement, Agenda, Document, GalleryAlbum, and VillageOfficial. Publishing modules report total/published/draft; officials report total/active/inactive. It fetches at most five future published agendas ordered by start_at, five latest news items, and five latest announcements. Soft-deleted rows are excluded by the models. The view handles zero rows and nullable agenda locations. These are dashboard summaries, not analytics.

### Blade component conventions

Reusable components live only under resources/views/components/admin:

- page-header: title, optional subtitle, optional actions slot.
- breadcrumb: a small array of label/url entries; the final item has no URL.
- card and stat-card: shared surface and summary patterns.
- status-badge: central status mapping.
- empty-state: optional text icon, title, message, and action slot.
- validation-summary: top-level validation alert; field components still show local feedback.
- table and table-actions: Bootstrap responsive table wrapper and labelled action area.
- search-form: GET search using search, preserves scalar query filters, removes page, and offers a clear link.
- filter-form: GET filter container that preserves an existing search term.
- sidebar-link: route-aware active link or semantic disabled item.
- confirm-button and confirmation-modal: one modal per admin layout, never one per row.
- date-text: semantic time output backed by App\Support\IndonesianDate.
- form/input, textarea, select, checkbox, and file: labels, required indicator, old input, help text, Bootstrap invalid state, accessible descriptions, and field errors. File supports existing-file text, selected filename, and optional local image preview.

Keep component APIs small. Use slots for page/card actions, card footer, table head/caption, and empty-state actions. Future CRUD forms follow POST for create, PUT/PATCH for update, and DELETE through method spoofing. Controllers remain responsible for validation and trusted fields.

Status badges map draft to Draf/secondary, published to Terbit/success, active to Aktif/success, inactive to Nonaktif/secondary, and expired to Kedaluwarsa/warning. Unknown values render their original label using a neutral bordered badge.

AppServiceProvider configures Laravel pagination with Paginator::useBootstrapFive(). Future list pages use model pagination links rather than custom pagination markup.

### Confirmation, files, and JavaScript

The admin layout renders one accessible Bootstrap confirmation modal. A future destructive button supplies data-confirm-action, data-confirm-item, and optional data-confirm-message. Central JavaScript validates that an action exists, assigns it to the modal form, inserts labels with textContent, and resets modal state after closing. The form contains CSRF and Laravel DELETE method spoofing. Future modules must still authorize deletion server-side.

File components are presentation only. Central JavaScript reports the selected filename and creates a temporary object URL for a selected local image. It revokes replaced preview URLs. It performs no upload, validation, processing, storage, or deletion.

### CSS, dates, and accessibility

Bootstrap remains the layout foundation. Shared/public tokens stay in resources/css/app.css; admin-only rules are in resources/css/admin.css and loaded only by the login/admin layouts. Vite has explicit app.css, admin.css, and app.js inputs. The design system defines primary, primary-dark/light, surface, background, border, text, muted, success, warning, danger, and info colors. It provides typography for page/card/table/form/metadata/empty states, consistent cards, focus visibility, responsive tables, mobile action behavior, Bootstrap pagination colors, and reduced-motion handling. No icon package was added; meaningful text labels are used.

App\Support\IndonesianDate plus x-admin.date-text is the date-display convention: 17 September 2026 or 17 September 2026, 09:00. Null dates render an em dash. Numbers use PHP number_format with Indonesian separators where displayed.

All interactive controls retain text or accessible labels. Skip navigation, semantic headings, labelled modal relationships, visible focus, status live regions for ordinary flash messages, alert roles for errors/warnings, and semantic disabled navigation are built into the foundation.

Phase 3 verification passes 67 tests / 316 assertions, Blade compilation, Pint, Composer validation, npm install/audit, production Vite build, route inspection, application inspection, and real HTTP login/dashboard/assets/logout checks. Browser automation failed to start twice because its Windows sandbox helper exited, so pixel-level desktop/mobile visual inspection remains pending. Static responsive rules, Bootstrap offcanvas markup, and behavior-focused tests were verified. Phase 4 — News CMS requires external review and explicit authorization.

