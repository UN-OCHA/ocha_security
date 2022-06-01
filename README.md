# Ocha Security

This is primarily a helper for the seckit module. More security-related fixes
that apply to all sites would be welcome.

CSP rules are a bit confusing. Add to or improve these notes if they make it
more so.

## Seckit module helper.
Prepares hashes, or a nonce for logged-in users, to allow CSP protection for
scripts. Requires the seckit module, with [this patch](seckit-patch).
(Note, the patch might soon be replaced, check status on [the ticket](patch-ticket)).

This is necessary to avoid rules allowing the use of 'eval' or 'unsafe-inline',
for 'script-src' which undermine the point of using the seckit module.

Note that it is recommended to use 'unsafe-inline' if a nonce or a hash is
included, as this will work with CSP level 1 browsers but will be ignored by
CSP level 2 browsers. [Not the best reference](unsafe-inline)

For logged-in users, where the page is not cached, a __nonce__ (Number used ONCE)
must be generated for each request, but can be used for all of the scripts.

For anonymous users, where the page is cached, a __hash__ must be created for each
separate script. Inline scripts use a hash of the script itself, attached files
use a hash of the filename. These can be re-used across requests.

This adds hashes or a nonce to script elements, assets and attachments, and the
same to the CSP directives.

## Notes

@todo Find a resource to explain what Drupal means by 'element', 'asset' and
'attachment'.

Adds nonce to/ creates hash for scripts as:
Elements (via pre-render hook)
`src/Element/OchaSecurityHtmlPreRender.php`
Assets
`src/Asset/OchaSecurityAssetResolver.php`
Attachments
`ocha_security_page_attachments_alter()`

Also ensures the sameSite=Lax header for cookies, though this is now default
behavior in modern browsers
`src/Session/OchaSecuritySessionConfiguration.php`

Hashes and nonces require extra work - they "are only intended for cases where
removing inline scripts is not an option" ([source](https://blog.mozilla.org/security/2014/10/04/csp-for-the-web-we-have/)). We might consider using only hashes and caching them.

['strict-dynamic'](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src#strict-dynamic) overrides any allowed domains in the seckit configuration, but
only for browsers which implement CSP-v3. So a nonce or hash is necessary for
all scripts.

[seckit-patch]: https://www.drupal.org/files/issues/2021-09-13/2844205-alter-csp-directives-10.patch
[patch-ticket]: https://www.drupal.org/project/seckit/issues/2844205#comment-14455849
[unsafe-inline]: https://github.com/mozilla/http-observatory/issues/88
