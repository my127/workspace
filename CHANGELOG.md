# Changelog

## 0.2.0 (2022-01-26)

- Support asking for filepaths during `ws create` (#63) (9a23db9) by Kieren Evans
- Hardcode global service commands (#94) (e384783) by Patrick
- Add support for multiple harness layers in a workspace (#93) (25667ec) by andytson-inviqa
- Phpstan Level 5 (#107) (dbe7ac0) by dantleech
- Refactor Package Repository (#105) (7c9e4d9) by dantleech
- Introduce PHP-CS-Fixer (#98) (b7eab3e) by dantleech
- Validate composer.lock is up to date with the composer.json file (#99) (bea4ff7) by Kieren Evans
- Fix null clone on wrong options (#89) (973a4e0) by Kieren Evans
- Wrap exception and add `var-dumper` as a dev dependency (#104) (b439ae7) by dantleech
- Fix optional command options (#87) (ec406c2) by Kieren Evans
- Add optional description to command type (#95) (d6eed11) by Patrick
- Switch changelog generator docker image (#92) (926ec61) by Kieren Evans
- Introduce Github Actions (#97) (2c6acc9) by dantleech
- Allow direct use of archive URLs in harness attribute (#74) (86fa13d) by James Halsall
- Fix poweroff command not being available in a harness directory (#86) (77c8cfe) by Kieren Evans
- Support for PHP 8.0

## [0.2.0-rc.1](https://github.com/my127/workspace/tree/0.2.0-rc.1) (2021-05-19)

[Full Changelog](https://github.com/my127/workspace/compare/0.1.3...0.2.0-rc.1)

**Implemented enhancements:**

- Add a cheatsheet [\#83](https://github.com/my127/workspace/pull/83) ([rgpjones](https://github.com/rgpjones))
- Add poweroff command [\#82](https://github.com/my127/workspace/pull/82) ([kierenevans](https://github.com/kierenevans))
- Add before/after overlay events and match with before for prepare [\#78](https://github.com/my127/workspace/pull/78) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Improve integration test suite [\#77](https://github.com/my127/workspace/pull/77) ([dantleech](https://github.com/dantleech))
- Add PHP 8 support [\#68](https://github.com/my127/workspace/pull/68) ([elvetemedve](https://github.com/elvetemedve))
- Add config dump command [\#67](https://github.com/my127/workspace/pull/67) ([hgajjar](https://github.com/hgajjar))
- Add Jaeger daemon [\#66](https://github.com/my127/workspace/pull/66) ([kierenevans](https://github.com/kierenevans))
- Add more default symfony expression functions [\#56](https://github.com/my127/workspace/pull/56) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Update symfony components and twig to latest minor version [\#55](https://github.com/my127/workspace/pull/55) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Upgrade to mailhog image v1.0.1 [\#54](https://github.com/my127/workspace/pull/54) ([kierenevans](https://github.com/kierenevans))
- Fix multiple argument run/passthru escaping [\#53](https://github.com/my127/workspace/pull/53) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Make commands and function errors debuggable by saying what name they are [\#51](https://github.com/my127/workspace/pull/51) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Add missing Bash interpreter page [\#49](https://github.com/my127/workspace/pull/49) ([opdavies](https://github.com/opdavies))
- Misc: license as MIT [\#47](https://github.com/my127/workspace/pull/47) ([dcole-inviqa](https://github.com/dcole-inviqa))
- Note that curl is required [\#46](https://github.com/my127/workspace/pull/46) ([kierenevans](https://github.com/kierenevans))
- Add an after\('harness.prepare'\) event [\#44](https://github.com/my127/workspace/pull/44) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Test on PHP 7.4 [\#40](https://github.com/my127/workspace/pull/40) ([kierenevans](https://github.com/kierenevans))

**Fixed bugs:**

- Add requirement to use prefix space to avoid secrets in shell history [\#65](https://github.com/my127/workspace/pull/65) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Handle sidekick errexiting correctly [\#58](https://github.com/my127/workspace/pull/58) ([andytson-inviqa](https://github.com/andytson-inviqa))

**Closed issues:**

- Service commands provide no feedback if command not found [\#62](https://github.com/my127/workspace/issues/62)
- Support for self-update? [\#60](https://github.com/my127/workspace/issues/60)
- Executing a ws command that does not exist should return a non-zero exit code [\#45](https://github.com/my127/workspace/issues/45)
- Support for multiple harnesses to one workspace [\#23](https://github.com/my127/workspace/issues/23)

## [0.1.3](https://github.com/my127/workspace/tree/0.1.3) (2020-01-07)

[Full Changelog](https://github.com/my127/workspace/compare/0.1.2...0.1.3)

**Implemented enhancements:**

- Turn off html autoescape [\#33](https://github.com/my127/workspace/pull/33) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Reduce return-type constraints of dynamic functions [\#32](https://github.com/my127/workspace/pull/32) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Prompt user to check they can run the command [\#27](https://github.com/my127/workspace/pull/27) ([bekapod](https://github.com/bekapod))
- Make twig dynamic functions use optional arguments [\#26](https://github.com/my127/workspace/pull/26) ([andytson-inviqa](https://github.com/andytson-inviqa))
- Update log legend [\#24](https://github.com/my127/workspace/pull/24) ([bennoislost](https://github.com/bennoislost))

**Fixed bugs:**

- Bump twig version [\#39](https://github.com/my127/workspace/pull/39) ([dcole-inviqa](https://github.com/dcole-inviqa))
- Fix incorrect error message due to overwriting of $version [\#38](https://github.com/my127/workspace/pull/38) ([g-foster2](https://github.com/g-foster2))
- Ensure libsodium is installed on travis and test on 7.3 and 7.4 [\#28](https://github.com/my127/workspace/pull/28) ([dantleech](https://github.com/dantleech))
- Fix subscribers that use environment variables. [\#25](https://github.com/my127/workspace/pull/25) ([kierenevans](https://github.com/kierenevans))

**Closed issues:**

- Add ws assets sync command [\#31](https://github.com/my127/workspace/issues/31)

## [0.1.2](https://github.com/my127/workspace/tree/0.1.2) (2019-04-22)

[Full Changelog](https://github.com/my127/workspace/compare/0.1.1...0.1.2)

**Implemented enhancements:**

- Mention sodium extension requirement - some distributions don't enable it! [\#20](https://github.com/my127/workspace/pull/20) ([kierenevans](https://github.com/kierenevans))
- Upgrade python in AWS image [\#19](https://github.com/my127/workspace/pull/19) ([kierenevans](https://github.com/kierenevans))

**Fixed bugs:**

- Fix stty size - fixes line wrapping in `ws console` [\#22](https://github.com/my127/workspace/pull/22) ([kierenevans](https://github.com/kierenevans))
- Allow tests to be run on macOS [\#21](https://github.com/my127/workspace/pull/21) ([kierenevans](https://github.com/kierenevans))

**Closed issues:**

- Can't build aws-cli docker image anymore [\#18](https://github.com/my127/workspace/issues/18)
- fix bug whereby passthru does not respect exit codes [\#14](https://github.com/my127/workspace/issues/14)

## [0.1.1](https://github.com/my127/workspace/tree/0.1.1) (2019-04-07)

[Full Changelog](https://github.com/my127/workspace/compare/0.1.0...0.1.1)

**Fixed bugs:**

- ensure bash interpreter exits with correct error code [\#16](https://github.com/my127/workspace/pull/16) ([dcole-inviqa](https://github.com/dcole-inviqa))



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
