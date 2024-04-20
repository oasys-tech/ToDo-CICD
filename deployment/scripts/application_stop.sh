#!/bin/bash

if [[ -n $(pgrep httpd) ]]; then
  systemctl stop httpd
fi