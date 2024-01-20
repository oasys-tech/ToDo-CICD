# CICDを構築する
## larastanでソースコード解析をする
1. `composer require --dev nunomaduro/larastan`
1. phpstanの設定ファイルを作成する
   ```yaml
   includes:
       - ./vendor/nunomaduro/larastan/extension.neon
   
   parameters:
       level: 7
       paths:
           - app/Http/Controllers
           - app/Models
       ignoreErrors:
   ```
1. `./vendor/bin/phpstan analyse -c phpstan.neon`

## 単体テストコードを作成する
1. phpunit.xmlの設定を変更する
   ```xml
    <php>
        ...
        <server name="DB_CONNECTION" value="sqlite"/>
        <server name="DB_DATABASE" value=":memory:"/>
        ...
    </php>
   ```
2. 単体テストコードを作成する
3. 単体テストコードを実行する  
   `php artisan test`
