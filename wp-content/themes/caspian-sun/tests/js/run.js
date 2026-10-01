// Unit tests for assets/js/contact.js validators (no browser): node tests/js/run.js
// Must agree with tests/php/run.php — both read tests/validation-cases.json.
const fs = require('fs'), vm = require('vm'), path = require('path');
const root = path.join(__dirname, '..', '..');
let src = fs.readFileSync(path.join(root, 'assets/js/contact.js'), 'utf8');
src = src.replace('/* --------------------------------------------------------------- helpers */', 'globalThis.__r = rules; globalThis.__c = cleaners;');
const form = { addEventListener() {}, querySelector() { return null; }, getAttribute() { return '{"A":["1 kg"],"B":[]}'; },
  elements: new Proxy({}, { get: () => ({ addEventListener() {}, value: '' }) }) };
const ctx = { document: { getElementById: () => form, head: {} }, window: { CSP_FORM: {} }, console };
ctx.globalThis = ctx; vm.createContext(ctx);
try { vm.runInContext(src, ctx); } catch (e) { /* the DOM-dependent tail is not needed */ }
const R = ctx.__r, C = ctx.__c;
let pass = 0, fail = 0;
const t = (n, ok, i = '') => { if (ok) pass++; else { fail++; console.log('FAIL:', n, i); } };
const cases = JSON.parse(fs.readFileSync(path.join(root, 'tests/validation-cases.json'), 'utf8'));
for (const [f, v, exp] of cases) t(`matrix ${f} ${JSON.stringify(v.slice(0, 30))}`, (R[f](v) === '') === exp, R[f](v));
t('name cleaner collapses spaces', C.name('  John    Smith ') === 'John Smith');
t('phone cleaner collapses spaces', C.phone(' 722   852 8095 ') === '722 852 8095');
t('email cleaner trims', C.email(' a@b.co ') === 'a@b.co');
t('subject cleaner one line', C.subject('Hello\n  World') === 'Hello World');
t('message cleaner keeps newline', C.message('  A1\r\nB2 ') === 'A1\nB2');
t('name 100 ok / 101 rejected', R.name('a'.repeat(100)) === '' && R.name('a'.repeat(101)) !== '');
t('phone 7 ok / 6 rejected', R.phone('1234567') === '' && R.phone('123456') !== '');
t('phone 15 ok / 16 rejected', R.phone('123456789012345') === '' && R.phone('1234567890123456') !== '');
t('message 3000 ok / 3001 rejected', R.message('a'.repeat(3000)) === '' && R.message('a'.repeat(3001)) !== '');
t('subject 150 ok / 151 rejected', R.subject('a'.repeat(150)) === '' && R.subject('a'.repeat(151)) !== '');
t('email consecutive dots rejected', R.email('a..b@example.com') !== '');
t('email local part 65 rejected', R.email('a'.repeat(65) + '@example.com') !== '');
t('normal sentence "select ... from" accepted', R.message('Please select the product from your list.') === '');
for (const bad of ['<svg onload=alert(1)>', 'javascript:alert(1)', '{{7*7}}', '${jndi:ldap://x}', '1 UNION SELECT password FROM users', "x' OR '1'='1", '<!-- hi -->'])
  t('injection rejected: ' + bad, R.message(bad) !== '');
t('product: unknown rejected', R.product('Nope') !== '' && R.product('') === '');
console.log(`\n${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);
