# serverless-wordpress

AWS Lambda, Aurora Serverless, Amazon EFS を使って WordPress を動作させます。
AWS SAM を使用しています。

## 事前準備

作業環境に下記のものを導入しておきます。

- [AWS CLI バージョン2](https://docs.aws.amazon.com/ja_jp/cli/latest/userguide/install-cliv2.html)
- [AWS CLI 用の Session Manager Plugin](https://docs.aws.amazon.com/ja_jp/systems-manager/latest/userguide/session-manager-working-with-install-plugin.html)
- [AWS SAM CLI](https://docs.aws.amazon.com/ja_jp/serverless-application-model/latest/developerguide/serverless-sam-cli-install.html)

## 導入

AWS SAM CLI で環境を構築します。

```bash
sam deploy --guided
```

AWS SAM CLI の出力に `HttpApiEndpoint` が表示されるので、ブラウザでアクセスすると WordPress の初期設定が行えます。
下記の値については、AWS Secrets Manager の `wordpress-database` を参照して入力してください。

- データベース名
- データベースのユーザー名
- データベースのパスワード
- データベースホスト

手動で `wp-config.php` を作成・修正したい場合、踏み台 EC2 インスタンスに SSM Session Manager でアクセスして行います。
インスタンス ID は AWS SAM CLI の出力に `Instance` として表示されます。

```bash
aws ssm start-session --target <Instance>
```

```shell-console
sh-4.2$ cd /mnt
sh-4.2$ cp wp-config-sample.php wp-config.php
sh-4.2$ vi wp-config.php
```

修正が完了したら `HttpApiEndpoint` にブラウザでアクセスして初期設定の続きを行ってください。

不要になった踏み台 EC2 インスタンスは `template.yaml` の `Instance`, `InstanceRole`, `InstanceProfile` をコメントアウトし、再度 `sam deploy` することで削除が可能です。

## 既知の不具合

- PHP Layer For AWS Lambda + HTTP API ではバイナリーの HTTP Body を返せない(500 Internal Server Error になる)ため、画像が表示できない
    - 例
        - テーマ選択時にスクリーンショットが表示できない
    - 改修案
        - REST API にして BinaryMediaTypes を適切に設定する
        - パスにステージ名が含まれてしまい `handler.php` が上手く機能しなくなるので修正を行う
- WordPress の処理が 30 秒以内に終わらず、HTTP API + AWS Lambda 統合のタイムアウト(最大30秒)を超えてしまう
    - 例
        - 初期設定時、プラグインや WordPress の更新のような重い処理
        - コールドスタート時
    - 改修案
        - AWS Lambda を使用する限りは難しいと思われる
