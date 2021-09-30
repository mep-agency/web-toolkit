Skeleton
========

The skeleton project is used when a new project is initialized. It's just like launching ``symfony new PROJECT_NAME --full``
but with a twist! The purpose is to minimize the amount of manual operations to set up a new project.

Here's a list of the main features:

- More articulate ``.gitignore`` file
- SQLite database set up
- PhpStorm project's files (including database sync and Symfony plugin basic setup)
- GitHub actions
- Useful vendors and post create project commands
- Internationalization initial set up
- Authorization setup and User's implementation (entity, repository, fixtures and CRUD controller)
- Simple back office
- File storage system setup (be aware that prod environment has to be manually configured)