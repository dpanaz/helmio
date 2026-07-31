# Install the Helmio UI patch

Run from the repository root (`/workspaces/helmio`):

```bash
cp -R /path/to/helmio-ui-patch/. apps/api/
cd apps/api
sed -i 's/^APP_NAME=.*/APP_NAME=Helmio/' .env
php artisan optimize:clear
npm run build
php artisan test
```

Restart Laravel and Vite after installation.

Commit from the repository root:

```bash
cd /workspaces/helmio
git add .
git commit -m "Add Helmio contribution standards and branded dashboard"
git push origin main
```
