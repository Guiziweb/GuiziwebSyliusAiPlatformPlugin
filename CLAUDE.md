# CLAUDE.md

Sylius plugin that manages AI providers, models and agents from the admin. It is the shared base for the Guiziweb AI plugins (grid assistant, shopping assistant), built on Symfony AI.

## Commands

Docker is the default. Ports and Node version live in `compose.override.yml`.

```bash
ENV=dev make init            # containers, dependencies, assets
ENV=dev make database-init
ENV=dev make load-fixtures
make up / make down
make php-shell
```

## Tests

Same stack as the other Guiziweb plugins.

```bash
make phpunit    # unit, integration, functional (tests/)
make behat      # features/, one feature per user-facing behaviour
make phpstan    # level max
make ecs        # Sylius coding standard
```

- Every feature or fix ships with its tests
- Behat covers the admin, PHPUnit covers the services
- Never call a real LLM in tests: use fakes

## Architecture

To be written once the design is agreed.

## Commits

Conventional Commits, English, **subject line only, no body**: `type(scope): description`.

- `feat` bumps minor, `fix` bumps patch, `feat!` or `fix!` bumps major
- Other types: `refactor`, `test`, `docs`, `chore`, `ci`
- Keep the subject under 72 characters, imperative, no trailing period

## Releases

[release-please](https://github.com/googleapis/release-please) reads the commit history and opens the release PR. Never edit `CHANGELOG.md` or the version by hand.
