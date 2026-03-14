# Instructions for agents

## Code style

* Use functions instead of classes, and as much as possible, pure functions with no side effects.
* Ensure code passes lint checks, e.g with `npm run lint:css`, `npm run lint:js`, `npm run lint:md:docs`, `npm run lint:php`.
* Write unit tests for new functions.
* Don't cast variables, except in extreme circumstances (in which case the occurrence should be thoroughly documented). It is better to fail fast than coerce possibly corrupted values and land in an unknown state.

## Pull Requests

* PRs should be fewer than 500 lines in size.
* Make changes to public functions in a dedicated PR. Do not mix changes to public functions and internal functions in the same PR. 

### Pull request titles

PR titles should be succinct and thematic, not a complete summary of every change. Do not write titles as a comma-separated list of edits. Instead, choose a short title that reflects the main intent of the PR. The description should explain the detailed scope.

For example, prefer `Standardize hooks` over `Rename WordPress hook callbacks with _hook suffix, add @internal and Fired by docblocks, hardcode namespace`.

## Tests

* Tests should be in the global namespace. Deliberately add `use` statements to import the namespaced classes, functions, and constants used in the tests.
* Each test is self-contained. Never use shared `setUp` / `tearDown` or similar variants in PHPUnit. Each test is responsible for arranging the environment to be the exact state it needs.
* Use Arrange-Act-Assert, or (where applicable) Arrange-Expect-Act pattern. Use comment headings in the test code, e.g. `// Arrange.`, `// Act.`, `// Assert.`
* The test's function name should describe the test. A docblock should not be added to the test as that would create redundancy.
