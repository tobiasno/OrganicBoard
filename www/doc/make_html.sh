#!/bin/bash

asciidoc -b html5 --theme flask -a icons -a iconsdir=icons -a toc -a lang=de userguide.txt
