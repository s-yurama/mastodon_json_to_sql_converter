<h1>
  概要
</h1>

<p>
  Mastodonのエクスポートで出力したoutbox.jsonから投稿本文などのstatusesなどを復元するためのinsert文を生成するツールです。
  返信やブースト、メディアには対応しておらず、壊れたおひとり様マストドンを雑に救出する程度のツールです。
</p>

<h2>
  注意点
</h2>

<p>いくつかセキュリティ上のを含む致命的な問題を抱えているので、お持ち帰りの場合はご理解の上で。</p>
<ul>
  <li>Mastodonに直接に操作を行うのではなく、単純にSQLファイルを生成します。</li>
  <li>コンテナにpostgreSQLを含有しておらず標準の関数によるSQLの文字列エスケープをちゃんとしていません。このため、投稿本文からインジェクションが発生する可能性があるかもしれません。</li>
  <li>media_attachmentsはファイルの扱いまで対応していません。</li>
  <li>GUIを持ちません。Laravelのコマンドラインから動作します。</li>
  <li>既にデータがある場合の追加レストアを想定していません。</li>
  <li>リプライやブーストなどの対応をしていません。</li>
</ul>

<h2>
  使い方
</h2>

<ul>
  <li>先に、導入先のマストドンでユーザーだけ作成し、ユーザーIDを控えておいてください。<br/>※サーバー上でpsqlコマンドでDBに入って<code>select * from accounts</code>するなどで確認してください。</li>
  <li>任意のディレクトリに<code>git clone</code>してください。</li>
  <li><code>./src/app/Console/Commands/ConvertOutbox.php</code>内の<code>const ACCOUNT_ID</code>を作成済みのユーザーIDに書き換えます。</li>
  <li><code>docker</code>ディレクトリ内で、<code>docoker compose up</code>してください。</li>
  <li><code>./storage/app/json</code>下に、<code>outbox.json</code>を設置してください。</li>
  <li><code>docker exec -it docker_www_mjtsc_1 /bin/bash/</code>でコンテナに入ってください。</li>
  <li><code>cd /var/www/src/</code>でソースディレクトリに移動してください。</li>
  <li><code>php artisan convert:outbox</code>を実行してください。</li>
  <li><code>./storage/app/sql</code>下に、各種insert文が生成されます。</li>
</ul>

<h2>
   その他
</h2>

<p>
  実装は下記にあります。
  <code>./src/app/Console/Commands/ConvertOutbox.php</code>
</p>
<hr>

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
