#勤怠管理アプリ 

##環境構築  

###Docerビルド  
```  
git clone git@github.com:sa-0117/attendance-management.git
docker-compose up -d --build  
``` 

###Laravel環境構築    
```  
docker-compose exec php bash  
composer install  
.env.example  
php artisan key:generate  
php artisan migrate  
php artisan db:seed 
```  

##使用技術  

* PHP 7.4.9
* Laravel 8.83.8 
* MySQL 8.0  
* Mailtrap


##ER図

![er](https://github.com/user-attachments/assets/138491c1-1bd0-4d85-9709-101de566b42e)

##メール認証

mailtrapを使用しています。以下のリンクより会員登録をお願いします。

https://mailtrap.io/

メールボックスのIntegrationのCode Samples「PHP:Laravel 7.x and 8.x」を選択し、MAIL_MAILERからMAIL_ENCRYPTIONまでの項目をコピーして.envファイルにペーストしてください。

MAIL_FROM_ADDRESSについては任意のメールアドレスを入力してください。


##テストアカウント

* 一般ユーザー
  name:一般ユーザー
  email：user@example.com
  password：password123

* 管理者ユーザー
  email：testadmin@example.com
  password：adminpassword

###PHPUnitを利用したテストについて
  ```
  docker-compose exec mysql bash
  mysql -u root -p
  create database demo_test;
  
  docker-compose exec php bash
  php artisan migrate:fresh --env=testing
  ./vendor/bin/phpunit
  ```
  ※mysqlのパスワードは「root」



##URL  

* 開発環境：http://localhost/ 
* phpMyAdmin:http://localhost:8080
