<?php
/*
Plugin Name: Maintenance Mode
Description: Closing site for users with language selection
Version: 1.5
Author: n3oblog
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

function maintenance_mode() {
    if (get_option('maintenance_mode_enabled') == '1' && !current_user_can('manage_options') && !is_user_logged_in()) {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Retry-After: 3600');
        
        $lang = get_option('maintenance_mode_lang', 'ru');

        $translations = [
            'ru' => [
                'html_lang' => 'ru',
                'title'     => 'Технические работы',
                'header'    => 'Сайт временно недоступен. Ведутся технические работы.'
            ],
            'en' => [
                'html_lang' => 'en',
                'title'     => 'Maintenance Mode',
                'header'    => 'Temporarily Unavailable. Maintenance Work.'
            ]
        ];

        $current_text = isset($translations[$lang]) ? $translations[$lang] : $translations['ru'];
        
        echo '<!DOCTYPE html>
        <html lang="' . esc_attr($current_text['html_lang']) . '">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . esc_html($current_text['title']) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 50px;
                    background-color: #f2f2f2;
                }
                .message {
                    display: inline-block;
                    padding: 30px;
                    background: #fff;
                    border: 1px solid #ccc;
                    border-radius: 10px;
                    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                }
                h1 {
                    color: #333;
                    font-size: 24px;
                    margin: 0;
                }
            </style>
        </head>
        <body>
            <div class="message">
                <h1>' . esc_html($current_text['header']) . '</h1>
            </div>
        </body>
        </html>';
        exit();
    }
}
add_action('template_redirect', 'maintenance_mode');

function maintenance_mode_menu() {
    add_menu_page(
        'Maintenance Mode',        
        'Maintenance Mode',                    
        'manage_options',                 
        'maintenance-mode-settings',      
        'maintenance_mode_settings_page', 
        'dashicons-lock',          
        200                               
    );
}
add_action('admin_menu', 'maintenance_mode_menu');

function maintenance_mode_settings_page() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('maintenance_settings_nonce')) {
        $enabled = isset($_POST['maintenance_mode_enabled']) ? '1' : '0';
        update_option('maintenance_mode_enabled', $enabled);
        
        $lang = isset($_POST['maintenance_mode_lang']) && $_POST['maintenance_mode_lang'] === 'en' ? 'en' : 'ru';
        update_option('maintenance_mode_lang', $lang);

        echo '<div class="updated"><p>Настройки успешно обновлены. / Settings updated.</p></div>';
    }

    $is_enabled = get_option('maintenance_mode_enabled', '0');
    $current_lang = get_option('maintenance_mode_lang', 'ru');
    ?>
    <div class="wrap">
        <h1>⚙️ Maintenance Mode Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('maintenance_settings_nonce'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Включить режим тех. работ</th>
                    <td>
                        <input type="checkbox" name="maintenance_mode_enabled" value="1" <?php checked('1', $is_enabled, true); ?> />
                        <label for="maintenance_mode_enabled">Закрыть сайт от пользователей</label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Язык заглушки / Language</th>
                    <td>
                        <select name="maintenance_mode_lang" style="width: 150px;">
                            <option value="ru" <?php selected('ru', $current_lang); ?>>Русский (RU)</option>
                            <option value="en" <?php selected('en', $current_lang); ?>>English (EN)</option>
                        </select>
                        <p class="description">Выберите язык, на котором будет отображаться страница тех. работ.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Сохранить изменения'); ?>
        </form>
    </div>
    <?php
}
