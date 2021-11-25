This folder is designed for server wide scripts and supporting documents.
Project and/or individual customizations should be in a sub-folder titled by name of the server/project

Everybody used to point to "php" which caused a problem when the software changed.

In order to break everything, php has been renamed to php-dev
If a project needs specific changes:

o Make a branch on php-dev
o Test out the changes
o Commit/Merge into php-dev
o Tag the version
o Copy the php-dev treee to php-v[m.n]
o Update the config file to use php-v[m.n]

