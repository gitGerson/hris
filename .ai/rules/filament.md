---
paths:
  - 'app/Filament/**'
---

# Filament

## File uploads target R2: never call visibility()
Uploads go to Cloudflare R2 and are served from the custom domain in R2_URL.

1. One env var drives everything: FILESYSTEM_DISK sets both `filesystems.default` and Filament's `config('filament.default_filesystem_disk')` (vendor/filament/support/config/filament.php), which FileUpload, ImageEntry, ImageColumn, MarkdownEditor and exports all read. It is `r2` here; the code fallback stays `local` so a clone without credentials still works.

2. Never chain `->visibility('public')` on FileUpload. R2 has no S3 ACLs, so `setVisibility()` fails with `PutObjectAcl … AWS HTTP error`. Filament wraps that call in `rescue(..., report: false)`, so it fails silently and looks fine — it is a guaranteed-failing call, not a safeguard. Public access comes from the R2 custom domain, not per-object ACLs.

3. Pin `->disk('r2')` explicitly on uploads whose files must stay on R2 (avatars, company logo), and use the same disk on the matching ImageEntry/ImageColumn so reads and writes cannot drift apart.

4. `Storage::url()` returns an R2_URL-based link; the S3 API endpoint is not publicly readable, so R2_URL must be set or every generated link fails.

5. The `s3` disk is kept separate for real AWS. Do not repoint it at R2.
