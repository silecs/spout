Contributing
============

All contributions are welcome to this project.

Running tests
-------------

```sh
vendor/bin/phpunit
```

Benchmarking
------------

Make sure the dev dependencies are installed (`composer install`),
and that ext/xdebug isn't enabled in the CLI PHP (it would slow down the benchmarks).

```sh
# Run the benchmark suite and set a reference point named "before"
vendor/bin/phpbench run --warmup=2 --retry-threshold=5 --iterations=10 --store --tag=before tests/benchmarks

# Make some changes...

# Benchmark and compare with the reference run
vendor/bin/phpbench run --warmup=2 --retry-threshold=5 --iterations=10 --report=aggregate --ref=before tests/benchmarks
```

How to contribute
-----------------

* **File an issue** - if you found a bug, want to request an enhancement, or want to implement something (bug fix or feature).
* **Send a pull request** - if you want to contribute code. Please be sure to file an issue first.

## Pull request best practices

We want to accept your pull requests. Please follow these steps:

### Step 1: File an issue

Before writing any code, please file an issue stating the problem you want to solve or the feature you want to implement. This allows us to give you feedback before you spend any time writing code. There may be a known limitation that can't be addressed, or a bug that has already been fixed in a different way. The issue allows us to communicate and figure out if it's worth your time to write a bunch of code for the project.

Please take the time to add everything that could be useful to understnding your issue:

- Is it a bug or a feature?
- What were you expecting? What happened?
- Any file or code needed to reproduce the problem?

### Step 2: Fork this repository in GitHub

This will create your own copy of our repository.

### Step 3: Add the upstream source

The upstream source is the project under the Box organization on GitHub. To add an upstream source for this project, type:

```sh
git remote add upstream git@github.com:silecs/spout.git
```

This will come in useful later.

### Step 4: Create a feature branch

Create a branch with a descriptive name, such as `add-search`.

### Step 5: Push your feature branch to your fork

As you develop code, continue to push code to your remote feature branch. Please make sure to include the issue number you're addressing in your commit message, such as:

```sh
git commit -m "Adding search (fixes #123)"
```

This helps us out by allowing us to track which issue your commit relates to.

Keep a separate feature branch for each issue you want to address.

### Step 6: Rebase

Before sending a pull request, rebase against upstream, such as:

```sh
git fetch upstream
git rebase upstream/master
```

This will add your changes on top of what's already in upstream, minimizing merge issues.

### Step 7: Run the tests

Make sure that all tests are passing before submitting a pull request.
```sh
./vendor/bin/phpunit
```

### Step 8: Fix code style

Run the following command to check the code style of your changes:
```sh
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --verbose --diff --dry-run
```

This will print a diff of proposed code style changes. To apply these suggestions, run the following command:
```sh
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
```

### Step 9: Send the pull request

Send the pull request from your feature branch to us. Be sure to include a description that lets us know what work you did.

Keep in mind that we like to see one issue addressed per pull request, as this helps keep our git history clean and we can more easily track down issues.
