# Развертывание на Beget

## Шаги по развертыванию Laravel приложения на Beget

1. **Загрузка файлов:**
   - Создайте директорию `public_html` на сервере Beget (если не существует)
   - Загрузите содержимое папки `gateway/public/` в `public_html/` (index.php, .htaccess и т.д.)
   - Загрузите остальные файлы проекта (app/, bootstrap/, config/, database/, resources/, routes/, storage/, vendor/ и т.д.) в директорию выше `public_html`, например в корень аккаунта или в отдельную папку `laravel/`
   - Убедитесь, что `.env` загружен в ту же директорию, где находится `artisan`
   - Исключите: `node_modules`, `storage/logs/*`, `.git`

2. **Настройка .env файла:**
   - Файл `.env` уже настроен для production. Убедитесь, что установлены параметры базы данных:
     ```
     DB_CONNECTION=mysql
     DB_HOST= # IP или хост базы данных Beget
     DB_PORT=3306
     DB_DATABASE= # Имя базы данных
     DB_USERNAME= # Пользователь БД
     DB_PASSWORD= # Пароль БД
     ```
   - APP_KEY уже сгенерирован
   - APP_URL, APP_ENV, APP_DEBUG уже настроены для production
   - SANCTUM_STATEFUL_DOMAINS настроен для домена Beget

3. **Установка зависимостей:**
   - В SSH или через панель Beget выполните:
     ```
     composer install --no-dev --optimize-autoloader
     ```

4. **Миграции и сиды:**
   - Выполните миграции:
     ```
     php artisan migrate --force
     ```
   - Запустите сиды (если нужно):
     ```
     php artisan db:seed --force
     ```

5. **Права на файлы:**
   - Установите права на директории:
     ```
     chmod -R 755 storage
     chmod -R 755 bootstrap/cache
     ```

6. **Символьные ссылки:**
   - Создайте ссылку на storage:
     ```
     php artisan storage:link
     ```

7. **Кэширование:**
   - Для production оптимизируйте:
     ```
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     ```

8. **Проверка:**
   - Убедитесь, что сайт доступен по https://false617.beget.tech
   - Проверьте API эндпоинты, например https://false617.beget.tech/api/rooms

## Примечания
- Убедитесь, что PHP версия >= 8.2
- Включите mod_rewrite в Apache (обычно включено по умолчанию)
- Для HTTPS используйте SSL сертификат от Beget</content>
