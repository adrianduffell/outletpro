# Instructions for agents

## Code style

* Use functions instead of classes, and as much as possible, pure functions with no side effects.
* Ensure code passes lint checks, e.g with `npm run lint:css`, `npm run lint:js`, `npm run lint:md:docs`, `npm run lint:php`.
* Write unit tests for new functions.

## Pull Requests

* PRs should be fewer than 500 lines in size.

## Tests

* Each test is self-contained. Never use `setUp` / `tearDown` or similar variants in PHPUnit.
* Use Arrange-Act-Assert, or (where applicable) Arrange-Expect-Act pattern. Use comment headings in the test code, e.g. `// Arrange.`, `// Act.`, `// Assert.`
