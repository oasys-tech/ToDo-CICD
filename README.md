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
   1. カバレッジレポート対象を設定する
      ```xml
      <coverage processUncoveredFiles="true">
          <include>
              <directory suffix=".php">./app/Http/Controllers</directory>
              <directory suffix=".php">./app/Http/Requests</directory>
              <directory suffix=".php">./app/Models</directory>
          </include>
      </coverage>
      ```
   2. 環境変数を設定し、インメモリDBを利用する
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

## CIワークフローを作成する
1. .github/workflows/test-and-deploy.ymlを作成する
2. ベースコンテナを指定する
   ```yaml
   jobs:
      test:
          runs-on: ubuntu-latest
   ```
3. ソースコードをチェックアウトする
   ```yaml
   jobs:
      steps:
        - name: Checkout code
          uses: actions/checkout@v2
   ``` 
4. アプリケーションをセットアップする
   ```yaml
   jobs:
      steps:
          - name: Setup PHP
            uses: shivammathur/setup-php@v2
            with:
              php-version: '8.0'
              extensions: gd

          - name: Install Composer Dependencies
            run: composer install --no-progress --no-suggest --prefer-dist
            working-directory: ./

          - name: Copy env.testing file
            if: always()
            run: cp ./.env.example ./.env
            working-directory: ./

          - name: Clear Application Cache
            if: always()
            run: php artisan cache:clear
            working-directory: ./
   ``` 
   
3. 静的解析を実行する
   ```yaml
   jobs:
      steps:
        - name: Check with PHPStan
          if: always()
          run: vendor/bin/phpstan analyse --error-format=github --configuration=phpstan.neon
          working-directory: ./
   ```
4. 単体テストを実行する
   ```yaml
   jobs:
      steps:
          - name: Run Unit Tests
            if: always()
            run: vendor/bin/phpunit --coverage-html tests/Report
            working-directory: ./
   ```
5. カバレッジレポートをアップロードする
   ```yaml
   jobs:
      steps:
          - name: Upload Coverage Report
            if: always()
            uses: actions/upload-artifact@v2
            with:
              name: coverage
              path: ./tests/Report/*
              retention-days: 7
   ```
