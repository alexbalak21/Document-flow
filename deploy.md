# 🚀 AlwaysData Deployment Instructions (Laravel + Vite + Browsershot)

## 1. Build Everything Locally (YOUR PC)

Run these commands on your computer:

npm install  
npm run build  
composer install --no-dev  
php artisan optimize  

This generates:
- public/build/
- vendor/
- optimized Laravel config

---

## 2. Create Deployment ZIP

Include ONLY:

app/  
bootstrap/  
config/  
database/  
resources/  
routes/  
storage/  
vendor/  
public/  
artisan  
composer.json  
composer.lock  
vite.config.js  

Exclude:

node_modules/  
tests/  
.git/  
storage/logs/  
storage/framework/cache/  

---

## 7z Command for Deployment Archive

Use the following command to create the deployment ZIP containing all required Laravel directories and files:

7z a app_build.zip app bootstrap config database resources routes storage vendor public artisan composer.json composer.lock vite.config.js

---

## 3. Upload ZIP to AlwaysData

Upload app.zip to:

/home/USERNAME/

---

## 4. SSH Into AlwaysData

ssh USERNAME@ssh-USERNAME.alwaysdata.net

---

## 5. Remove Old App

rm -rf app

---

## 6. Unzip New App

unzip app.zip -d app

If unzip is missing:

7z x app.zip -oapp

---

## 7. Configure AlwaysData

PHP version: 8.2 or 8.3  
Web root: /home/USERNAME/app/public

---

## 8. Clear Laravel Cache

cd ~/app  
php artisan key:generate
php artisan config:clear
php artisan cache:clear
php artisan route:clear  
php artisan view:clear  
php artisan optimize  
php artisan storage:link  

---

## 9. Run Migrations

php artisan migrate --force

---

## 9.5 Create new user

php artisan tinker

App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@mail.com',
    'password' => bcrypt('Password123@'),
]);


---

## 10. Seed Products (Optional)

php artisan db:seed --class=ProductSeeder

---



---

## 11. Test Browsershot (Optional)

Add temporary route:

Route::get('/test-pdf', function () {
    return Browsershot::html('<h1>Hello</h1>')
        ->format('A4')
        ->showBackground()
        ->emulateMedia('print')
        ->noSandbox()
        ->timeout(60)
        ->pdf();
});

---

## 🎉 Deployment Complete
