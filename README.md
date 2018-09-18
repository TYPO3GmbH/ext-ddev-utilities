# ddev utilities

This extension for TYPO3 adds utilities to make working with
ddev by drud easier.

If you want to contribute, feel free to create PRs here.

## Utilities

...

## Commands


#### Export available databases

`bin/typo3cms ddev:exportdb` exports all configured databases.

The export is rather clever and exports the structure of all tables (so `ddev importdb` works) but leaves 
out all unnecessary data like caching tables, session tables etc.

Optional arguments:
`snapshot` stores a **copy** of the exported data into a timestamped folder

Usage:
`bin/typo3cms ddev:exportdb snapshot`

Note:
This command currently relies on mysqldump, so results may vary (in the sense that I doubt it'll do **anything** on MSSQL :)