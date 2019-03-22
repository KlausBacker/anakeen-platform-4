#!/usr/bin/env bash

npx yarn-deduplicate -l --packages @progress/kendo-ui,jquery,vue | grep Package; test $? -eq 1