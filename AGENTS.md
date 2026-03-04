## Code style

* Use functions instead of classes, and as much as possible, pure functions with no side effects.
* Ensure code passes lint checks, e.g with `npm run:lint:php` `npm run:lint.js`.
* Write unit tests for new functions.

## Pull Requests

* PRs should be under <500 lines in size.

## Tests

* Each test is self-contained. Never use setUp or tearDown in phpunit.
* Use Arrange-Act-Assert, or (where applicable) Arrange-Expect-Act pattern. Use comment headings in the test code, e.g. `//Arrange.`, `//Act.` //Assert. )
