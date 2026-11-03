# XarxesGest

XarxesGest is a modified version of [TryPost](https://github.com/trypostit/trypost)
(© Paulo Castellano and contributors), used and distributed under the
[GNU Affero General Public License v3.0](LICENSE.md).

Modifications by Soportic (since November 2026) include:

- Catalan (`ca`) interface language.
- Compatibility with current Claude models (no `temperature` sent to Anthropic).
- `xarxesgest:create-admin` command for the first self-hosted owner account.
- Time zone aliases normalized through ICU when the system lacks `tzdata-legacy`.
- MariaDB support (tests that assume a case-sensitive database are skipped there too).
- AI image generation in the composer (OpenAI, with the description written by Claude).
- CloudPanel deployment kit (`deploy/cloudpanel/`) and documentation in `docs/`.

The full history of changes is available in this repository's git log. Under
AGPL §13, users interacting with a modified version over a network must be
offered its corresponding source code.
