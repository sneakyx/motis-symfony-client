# AGENTS.md

Conventions for everyone (humans and AI agents) working in this repository.

## Communication

- Everything in this repository is English: issues, pull requests, code comments, commit messages.
- Issues and pull requests go to GitHub; work happens on feature branches named `feature/<issue>-<topic>`.
- The package author (sneakyx) reviews and merges pull requests. Never merge your own pull request.

## Code style

- PHP 8.2+, strict types everywhere, readonly properties where possible.
- Follow the style of the existing code; keep the public API small and typed.
- Namespaces: `Sneakyx\MotisSymfonyClient\` in `src/`, `Sneakyx\MotisSymfonyClient\Tests\` in `tests/`.
- The underlying API calls live in motis-php-client; this layer adds Symfony integration only.
- Generated models from the php client are not final, so `createMock` works for them; MotisClient itself is final, use the PSR-18 MockHttpClient pattern instead.

## Testing

- PHPUnit with a minimal test kernel (FrameworkBundle MicroKernelTrait); all tests offline with mocked PSR-18 clients.
- Run with `composer test`. CI runs the matrix over the supported Symfony versions.

## Workflow

1. Create an issue describing the change.
2. Branch off main, implement, add tests.
3. Open a pull request, wait for CI and review.