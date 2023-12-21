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
