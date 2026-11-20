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

The plugin wraps Symfony AI for Sylius: it adds a database and an admin, and reimplements nothing of the library.

**Providers.** A provider is a row (code, type, settings) whose settings are encrypted with Sylius's own encryption. The types and their fields are read from the AI bundle's own `platform` configuration (`AiConfigurationSchema`), so there is no provider list of our own. `PlatformRecipeReader` loads the bundle's extension to get the definition of a platform and turns it into a recipe, `PlatformRecipeExecutor` replays it with the provider's settings and calls the bridge's `Factory::createProvider()`. `AiProviderFactory` keeps one built provider per row and rebuilds it when its code, type or settings change. The plugin requires no bridge: the merchant installs the one they use.

**Agents.** An agent is an ordinary Symfony AI agent declared by its plugin in the `ai` configuration, without a prompt, and listed under `guiziweb_sylius_ai_platform.agents`. Its prompt, provider and model are a row (`Agent`) shipped by the plugin and edited by the merchant. For each listed agent, `AttachAgentRowProcessorPass` adds an `AgentRowProcessor` (an `ai.agent.input_processor`, priority -10); a listed agent that is missing or is a multi-agent stops the container build.

**A call.** `AgentRowProcessor` reads the row once, adds its prompt as the system message unless the caller already put one, and, when the agent has an enabled provider, passes the id of that provider in an internal call option (never the provider itself, so no key travels with the call) and sets the row's model unless the caller passed `model`. `ProviderRoutingListener`, on the library's `ModelRoutingEvent`, strips that option, loads the provider and routes the call to the one built by `AiProviderFactory`. The call stays on the agent's configured platform, so the profiler shows it. That platform must be built with an event dispatcher, otherwise the admin's provider is ignored.

**Updates.** `Agent::refreshDeclaration()` is what a plugin's migration or fixture calls: it refreshes the name and the default prompt, replaces the prompt only when the merchant has not customized it (it differs from the default), and never touches the provider or the model.

**Admin.** Sylius resources: providers (created by type) and agents (list and edit only, merchants do not create agents). A provider used by an agent cannot be deleted; Sylius shows its "in use" message.

**Tests.** The test application ships a `sample_assistant` agent on the library's mock platform.

## Commits

Conventional Commits, English, **subject line only, no body**: `type(scope): description`.

- `feat` bumps minor, `fix` bumps patch, `feat!` or `fix!` bumps major
- Other types: `refactor`, `test`, `docs`, `chore`, `ci`
- Keep the subject under 72 characters, imperative, no trailing period

## Releases

[release-please](https://github.com/googleapis/release-please) reads the commit history and opens the release PR. Never edit `CHANGELOG.md` or the version by hand.
