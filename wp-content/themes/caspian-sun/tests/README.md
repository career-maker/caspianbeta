# Unit tests

No WordPress or database needed.

    php -d extension=mbstring tests/php/run.php     # validation, mail/SMTP config, secrets, mailer HTML, URL-case helper
    node tests/js/run.js                             # browser-side validators (assets/js/contact.js)

Both suites read `tests/validation-cases.json` (the client's field validation matrix) so server and browser rules
cannot drift apart. Run both before every release.
