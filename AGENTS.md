# Instructions for agents

## Code style

* Use functions instead of classes, and as much as possible, pure functions with no side effects.
* Ensure code passes lint checks, e.g with `npm run lint:css`, `npm run lint:js`, `npm run lint:md-docs`, `npm run lint:php`.
* Write unit tests for new functions.
* Don't cast variables, except in extreme circumstances (in which case the occurrence should be thoroughly documented). It is better to fail fast than coerce possibly corrupted values and land in an unknown state.
* Fast-fail on error: prefer throwing exceptions for unrecoverable internal errors; only recover/continue at system boundaries (e.g. hooks/requests) after logging and returning a safe default.
* Guards: Use one condition per guard clause. Instead of combining multiple conditions with && or ||, write each guard as a separate if statement to improve readability and debugging.

## Logging

* Exception messages should be plain strings. No dynamic strings. Don't include any debugging info beyond a meaningful message.
* Log an error when catching a system-level failure in a try-catch block. Log: "Page ID could not be retrieved". Don't log: "Email address validation failed".
* When a WP_Error is encountered, consider logging before throwing an exception as the context won't be transferred to the exception.

## Pull Requests

* PRs should be fewer than 500 lines in size.
* Make changes to public functions in a dedicated PR. Do not mix changes to public functions and internal functions in the same PR.
* Include manual test instructions in the PR description. Provide required commands for copy/paste, e.g. `npm i` when packages change, or `wp option set ....` to arrange options into a pre-defined state.

### Pull request titles

PR titles should be succinct and thematic, not a complete summary of every change. Do not write titles as a comma-separated list of edits. Instead, choose a short title that reflects the main intent of the PR. The description should explain the detailed scope.

For example, prefer `Standardize hooks` over `Rename WordPress hook callbacks with _hook suffix, add @internal and Fired by docblocks, hardcode namespace`.

## Tests

* Tests should be in the global namespace. Deliberately add `use` statements to import the namespaced classes, functions, and constants used in the tests.
* Each test is self-contained. Never use shared `setUp` / `tearDown` or similar variants in PHPUnit, nor `test.beforeEach` / `test.afterEach` in Playwright. Each test is responsible for arranging the environment to be the exact state it needs.
* Use Arrange-Act-Assert, or (where applicable) Arrange-Expect-Act pattern. Use comment headings in the test code, e.g. `// Arrange.`, `// Act.`, `// Assert.`
* The test's function name should describe the test. A docblock should not be added to the test as that would create redundancy.
* Don't use `ob_start` in tests. Use PHPUnit's `expect*` methods and the Arrange-Expect-Act pattern. Use `expectException()` for exceptions and `expectOutputRegex()` or `expectOutputString()` for echo/printed output.
* When writing tests for hooked functions (those with a `_hook` suffix), use indirect WordPress routines to fire the hook rather than directly calling the hooked function. For example, if `my_title_hook()` hooks `the_title`, call WordPress' `get_the_title()` to test the whole integration.

### Mocks

* If it talks to the outside world, mock it; if it's part of the app or React, do not.
* Mock external boundaries (e.g. `@wordpress/api-fetch`), not React or internal logic.
* Define mock behaviour per test (e.g. in Jest, with `mockResolvedValue`), not globally (avoid global mocks where possible).
