# WoodMart PDF/UBL Invoices & Packing Slips

Плагін для генерації PDF та UBL інвойсів, а також накладних для WooCommerce з інтеграцією WoodMart теми.

## Особливості

- ✅ Генерація PDF інвойсів з використанням DOMPDF
- ✅ Генерація UBL XML інвойсів з використанням Sabre XML
- ✅ Генерація накладних (Packing Slips)
- ✅ Автоматичне прикріплення до email листів WooCommerce
- ✅ Настройки компанії та шаблонів
- ✅ Кастомні статуси замовлень
- ✅ Безпечне завантаження та зберігання файлів
- ✅ Мультимовна підтримка

## Структура плагіна

```
wp-content/plugins/woodmart-invoices/
├── woodmart-invoices.php          # Головний файл плагіна
├── composer.json                  # Залежності Composer
├── includes/                      # Основна логіка
│   ├── class-woodmart-invoices.php # Головний клас
│   ├── class-invoice-generator.php # Базовий генератор
│   ├── class-pdf-generator.php    # PDF генератор
│   ├── class-ubl-generator.php    # UBL генератор
│   ├── class-packing-slip-generator.php # Генератор накладних
│   ├── class-admin.php            # Адмін панель
│   ├── class-woocommerce.php      # WooCommerce інтеграція
│   ├── class-ajax.php             # AJAX обробка
│   └── functions.php              # Допоміжні функції
├── admin/                         # Адмін файли
│   ├── css/admin.css              # Адмін стилі
│   └── js/admin.js                # Адмін скрипти
├── templates/                     # Шаблони
│   ├── pdf/default.php            # PDF шаблон інвойсу
│   ├── packing-clips/default.php  # Шаблон накладної
│   ├── udp/                       # UBL шаблони
│   └── emails/                    # Email шаблони
├── assets/                        # Публічні ресурси
│   ├── css/                       # Публічні стилі
│   ├── js/                        # Публічні скрипти
│   └── images/                    # Зображення
├── languages/                     # Переклади
└── vendor/                        # Сторонні бібліотеки
```

## Встановлення

1. Завантажте плагін до папки `/wp-content/plugins/woodmart-invoices/`
2. Встановіть залежності Composer:
   ```bash
   composer install
   ```
3. Активуйте плагін в адмін панелі WordPress
4. Перейдіть до WooCommerce → WoodMart Invoices для налаштування

## Налаштування

### Загальні налаштування
- Назва компанії
- Адреса компанії
- Email компанії
- Телефон компанії
- Логотип компанії

### PDF/UBL Інвойси
- Увімкнення генерації PDF
- Увімкнення генерації UBL
- Формат номера інвойсу
- Шаблон інвойсу
- Email для прикріплення

### Накладні
- Увімкнення генерації накладних
- Шаблон накладної
- Кастомні статуси замовлень

## Використання

### Генерація вручну
На сторінці замовлення в адмін панелі ви знайдете кнопки:
- "Generate PDF Invoice" - генерація PDF інвойсу
- "Generate UBL Invoice" - генерація UBL інвойсу  
- "Generate Packing Slip" - генерація накладної

### Автоматична генерація
Документи автоматично генеруються та прикріплюються до email листів згідно з налаштуваннями.

### API використання

```php
// Генерація PDF інвойсу
$pdf_generator = WoodMart\Invoices\PDF_Generator::get_instance();
$pdf_path = $pdf_generator->generate_invoice( $order_id );

// Генерація UBL інвойсу
$ubl_generator = WoodMart\Invoices\UBL_Generator::get_instance();
$ubl_path = $ubl_generator->generate_invoice( $order_id );

// Генерація накладної
$packing_slip_generator = WoodMart\Invoices\Packing_Slip_Generator::get_instance();
$packing_slip_path = $packing_slip_generator->generate( $order_id );
```

## Хуки та фільтри

### Фільтри
```php
// Модифікація даних інвойсу
add_filter( 'woodmart_invoices_invoice_data', 'my_custom_invoice_data', 10, 2 );

// Модифікація даних компанії
add_filter( 'woodmart_invoices_company_info', 'my_custom_company_info' );

// Модифікація даних замовлення
add_filter( 'woodmart_invoices_order_data', 'my_custom_order_data', 10, 2 );
```

### Дії
```php
// Після генерації PDF
add_action( 'woodmart_invoices_pdf_generated', 'my_pdf_generated_action', 10, 2 );

// Після генерації UBL
add_action( 'woodmart_invoices_ubl_generated', 'my_ubl_generated_action', 10, 2 );

// Після генерації накладної
add_action( 'woodmart_invoices_packing_slip_generated', 'my_packing_slip_generated_action', 10, 2 );
```

## Кастомізація шаблонів

Ви можете створити власні шаблони в темі:
```
your-theme/woodmart-invoices/
├── pdf/
│   └── custom-invoice.php
├── packing-clips/
│   └── custom-packing-slip.php
└── emails/
    └── custom-email.php
```

## Безпека

- Всі згенеровані файли зберігаються в захищеній папці
- Доступ до файлів контролюється через nonces
- Санітизація всіх входів
- Перевірка прав доступу

## Вимоги

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+
- WoodMart тема (рекомендовано)

## Залежності

- `dompdf/dompdf` - генерація PDF
- `sabre/xml` - обробка XML для UBL

## Ліцензія

GPL v2 або новіша

## Автор

Xtemos - https://xtemos.com

## Підтримка

Для технічної підтримки зверніться до команди Xtemos. 