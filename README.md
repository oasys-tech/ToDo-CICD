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
   
5. 静的解析を実行する
   ```yaml
   jobs:
      steps:
        - name: Check with PHPStan
          if: always()
          run: vendor/bin/phpstan analyse --error-format=github --configuration=phpstan.neon
          working-directory: ./
   ```
6. 単体テストを実行する
   ```yaml
   jobs:
      steps:
          - name: Run Unit Tests
            if: always()
            run: vendor/bin/phpunit --coverage-html tests/Report
            working-directory: ./
   ```
7. カバレッジレポートをアップロードする
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

# CodeDeployをセットアップする
1. EC2にCodeDeployAgentをインストールする
2. CodeDeployをセットアップする
   1. サービスロールを作成する
   2. アプリケーションとデプロイグループを作成する
   3. リビジョン保管用のS3を作成する
3. appspec.yamlを作成する
   1. ApplicationStopのライフサイクルフックでWebサーバを終了する  
      `deployment/scripts/application_stop.sh`  
      ```shell
      if [[ -n $(pgrep httpd) ]]; then
        systemctl stop httpd
      fi
      ```
   2. AfterInstallのライフサイクルフックでアプリケーションをセットアップする  
      `deployment/scripts/after_install.sh`  
      ※実際のコードでは問題の解析のためにdeployment/deploy.logに実行結果をログ出力している
      1. デプロイしたリソースの権限を変更する
      ```shell
      semanage fcontext -a -t httpd_sys_rw_content_t /var/www/storage
      semanage fcontext -a -t httpd_sys_rw_content_t /var/www/bootstrap/cache
      chown apache:apache -R /var/www/
      ```
      2. バックエンドのセットアップとDBをマイグレートする
      ```shell
      cd /var/www
      composer install
      sudo -u apache php artisan migrate --force
      ```
      3. バックエンドのキャッシュを削除する
      ```shell
      sudo -u apache php /var/www/artisan cache:clear
      sudo -u apache php /var/www/artisan view:clear
      sudo -u apache php /var/www/artisan config:cache
      sudo -u apache php /var/www/artisan optimize
      sudo -u apache php /var/www/artisan route:cache
      ```
      4. フロントエンドのセットアップする
      ```shell
      sudo -u apache npm install
      set +e
      sudo -u apache npm run production
      set -e
      ```
   3. ApplicationStartのライフサイクルフックでWebサーバを開始する
      `deployment/scripts/application_start.sh`
      ```shell
      systemctl start httpd
      ```
   4. appspec.yamlを作成する  
      `appspec.yaml`  
      CodeDeployのライフサイクルフックに作成したシェルを割り当てる
   
# CDワークフローを作成する
1. デプロイ用ユーザを作成する
2. デプロイ用ユーザのアクセスキー/シークレットをGitHubに登録する
3. github/workflows/test-and-deploy.ymlに新規ステップを作成する
   ```yaml
    deploy:
      needs: test
      runs-on: ubuntu-latest
      if: (github.ref == 'refs/heads/main') && (github.event_name == 'push')
   ```
   
4. デプロイソースのzipを作成する
   ```yaml
      - name: Checkout code
        uses: actions/checkout@v2

      - name: Delete storage
        run: rm -rf ./src/storage

      - name: Zipping code
        run: zip -r todo_${{ github.run_id }}.zip .
   ```
5. デプロイソースをS3にアップロードする
   ```yaml
      - name: Configure AWS credentials
        uses: aws-actions/configure-aws-credentials@v1
        with:
          aws-access-key-id: ${{ secrets.AWS_ACCESS_KEY_ID }}
          aws-secret-access-key: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
          aws-region: ap-northeast-1

      - name: Upload source to S3
        run: aws s3 cp zeder_${{ github.run_id }}.zip s3://todo-revision-bucket --quiet
   ```
6. デプロイを開始する
   ```yaml
      - name: Registration app to CodeDeploy
        run: aws deploy register-application-revision --application-name todo --s3-location bucket=todo-revision-bucket,bundleType="zip",key=todo_${{ github.run_id }}.zip

      - name: Deploy app to EC2
        run: aws deploy create-deployment --application-name todo --deployment-group-name todoDeployGroup --file-exists-behavior "OVERWRITE" --s3-location bucket=todo-revision-bucket,bundleType="zip",key=todo_${{ github.run_id }}.zip
   ```
