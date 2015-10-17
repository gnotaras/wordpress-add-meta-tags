
# Copyright George Notaras

REL_FILES = [
    'add-meta-tags.pot',
	'add-meta-tags.php',
    'amt-admin-panel.php',
    'amt-settings.php',
    'amt-template-tags.php',
    'amt-utils.php',
    'amt-embed.php',
    'index.php',
    'AUTHORS',
    #'CONTRIBUTORS',
	'LICENSE',
	'NOTICE',
	'README.rst',
    'readme.txt',
#    'screenshot-1.png',
#    'screenshot-2.png',
#    'screenshot-3.png',
#    'screenshot-4.png',
    'uninstall.php',
    'wpml-config.xml',
]

REL_DIRS = [
    'templates',
    'metadata',
    'languages',
    'css',
    'js',
]

PROD_REL_FILES = [
	#'add-meta-tags.php',   # Do not include the main file. The combined file is auto added.
    'index.php',
    'AUTHORS',
	'LICENSE',
	'NOTICE',
	'README.rst',
    'readme.txt',
    'uninstall.php',
    'wpml-config.xml',
]

PROD_REL_DIRS = [
    'templates',
    'languages',
    'css',
    'js',
]

# When combining all php files to one, include these.
# Format: tuples of relative paths: ('path', 'to', 'file.php')
PHP_FILES_TO_COMBINE = [
    #('add-meta-tags.php', ),    # Do not include the main file. Automatically taken from PLUGIN_METADATA_FILE
    ('amt-admin-panel.php', ),
    ('amt-settings.php', ),
    ('amt-template-tags.php', ),
    ('amt-utils.php', ),
    ('amt-embed.php', ),
    ('metadata', 'amt_basic.php'),
    ('metadata', 'amt_dublin_core.php'),
    ('metadata', 'amt_extended.php'),
    ('metadata', 'amt_opengraph.php'),
    ('metadata', 'amt_schemaorg.php'),
    ('metadata', 'amt_twitter_cards.php'),
]

PLUGIN_METADATA_FILE = 'add-meta-tags.php'

# The line number after which the code starts in the PLUGIN_METADATA_FILE.
CODE_STARTS_AFTER_LINE = 50

POT_HEADER = """#  POT (Portable Object Template)
#
#  This file is part of the Add-Meta-Tags plugin for WordPress.
#
#  Read more information about the Add-Meta-Tags translations at:
#
#    http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Translations
#
#  Copyright (C) 2006-2015 George Notaras <gnot@g-loaded.eu>
#
#  Licensed under the Apache License, Version 2.0 (the "License");
#  you may not use this file except in compliance with the License.
#  You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS,
#  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#  See the License for the specific language governing permissions and
#  limitations under the License.
#
"""

# ==============================================================================

import sys
import os
import glob
import zipfile
import shutil
import subprocess
import polib
import StringIO
import re

def get_name_release():
    def get_data(cur_line):
        return cur_line.split(':')[1].strip()
    f = open(PLUGIN_METADATA_FILE)
    name = ''
    release = ''
    for line in f:
        if line.lower().startswith('plugin name:'):
            name = get_data(line)
        elif line.lower().startswith('version:'):
            release = get_data(line)
        if name and release:
            break
    f.close()
    
    if not name:
        raise Exception('Cannot determine plugin name')
    elif not release:
        raise Exception('Cannot determine plugin version')
    else:
        # Replace spaces in name and convert it to lowercase
        name = name.replace(' ', '-')
        name = name.lower()
        return name, release

name, release = get_name_release()


print 'Generating POT file...'

# Translation
pot_domain = os.path.splitext(PLUGIN_METADATA_FILE)[0]

# Generate POT file
args = ['xgettext', '--default-domain=%s' % pot_domain, '--output=%s.pot' % pot_domain, '--language=PHP', '--from-code=UTF-8', '--keyword=__', '--keyword=_e', '--no-wrap', '--package-name=%s' % pot_domain, '--package-version=%s' % release, '--copyright-holder', 'George Notaras <gnot@g-loaded.eu>']
# Add php files as arguments
for rf in REL_FILES:
    if rf.endswith('.php'):
        args.append(rf)
for rf in os.listdir('metadata'):
    if rf.endswith('.php'):
        args.append( os.path.join( 'metadata', rf ) )
for rf in os.listdir('templates'):
    if rf.endswith('.php'):
        args.append( os.path.join( 'templates', rf ) )
print (' ').join(args)

p = subprocess.Popen(args, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
stdout, stderr = p.communicate()

# Replace POT Header

f = open('%s.pot' % pot_domain, 'r')
pot_lines = f.readlines()
f.close()
f = open('%s.pot' % pot_domain, 'w')
f.write(POT_HEADER)
for n, line in enumerate(pot_lines):
    if n < 4:
        continue
    f.write(line)
f.close()

print 'Complete'

# Compile language .po files to .mo

print 'Compiling PO files to MO...'
for po_file in os.listdir('languages'):
    if not po_file.endswith('.po'):
        continue
    po_path = os.path.join('languages', po_file)
    print 'Converting', po_path
    po = polib.pofile(po_path, encoding='utf-8')
    mo_path = po_path[:-3] + '.mo'
    po.save_as_mofile(mo_path)

print 'Complete'
print

# Create production release

# Combine php files
# First get the main file that contains the plugin metadata
tmp_out = []
f = open(PLUGIN_METADATA_FILE)
for line in f:
    if not line.startswith('require'):
        tmp_out.append(line)
f.close()
combined_file = ''.join(tmp_out)
# Next combine the rest of files
for php_file_path_parts in PHP_FILES_TO_COMBINE:
    combined_file += '\n\n\n\n////////%s\n\n\n\n' % os.path.join(*php_file_path_parts)
    combined_file += open(os.path.join(*php_file_path_parts)).read()
# Write files
open('a1.php', 'wb').write(combined_file)

# Strip comments and php tags
tmp_out = []
for n, line in enumerate(StringIO.StringIO(combined_file)):
    line_stripped = line.strip()
    if n < CODE_STARTS_AFTER_LINE:
        tmp_out.append(line)    # Add plugin metadata and license info
    elif not line_stripped.startswith('<?php') and not line_stripped.startswith('//') and not line_stripped.startswith('* ') and not line_stripped == '*':
        tmp_out.append(line)
combined_file = ''.join(tmp_out)

# Strip empty multiline comments
combined_file = re.sub(r'/\*[\*\s]*?\*/', '', combined_file, 0, re.DOTALL)


DIRECT_ACCESS_CODE = '''if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}
'''
# Remove all instances of the DIRECT_ACCESS_CODE from the combined file
combined_file = combined_file.replace(DIRECT_ACCESS_CODE, '')
# Now add the DIRECT_ACCESS_CODE in the beginning
tmp_out = []
code_added = False
for n, line in enumerate(StringIO.StringIO(combined_file)):
    if not code_added and n + 1 > CODE_STARTS_AFTER_LINE:
        tmp_out.append(DIRECT_ACCESS_CODE)
        code_added = True
    tmp_out.append(line)
combined_file = ''.join(tmp_out)

#$text = preg_replace('!/\*.*?\*/!s', '', $text);
#$text = preg_replace('/\n\s*\n/', "\n", $text);

# More than two consecutive linefeeds become 2 consecutive linefeeds
combined_file = re.sub(r'\n\n\n+', '\n\n', combined_file, 0)
# Remove comments at the end of the line
#combined_file = re.sub(r'(?:(?!:)//.*)$', '', combined_file, 0, re.MULTILINE)
# Remove trailing spaces
#combined_file = re.sub(r'\s+$', '', combined_file, 0, re.MULTILINE)

open('a2.php', 'wb').write(combined_file)


for package_name in (name, name + '-dev'):
    # Set some variables according to the package we are generating
    # If this is the production package
    if package_name == name:
        print 'Creating the official production distribution package...'
        FILE_LIST = PROD_REL_FILES
        DIR_LIST = PROD_REL_DIRS
        has_combined = True
    else:
        print 'Creating the official development distribution package...'
        FILE_LIST = REL_FILES
        DIR_LIST = REL_DIRS
        has_combined = False

    # Create release dir and move release files inside it
    os.mkdir(package_name)

    # If this is the production package, add the combined file
    if has_combined:
        open(os.path.join(package_name, PLUGIN_METADATA_FILE), 'wb').write(''.join(tmp_out))
    # Copy files
    for p_file in FILE_LIST:
        shutil.copy(p_file, os.path.join(package_name, p_file))
    # Copy dirs
    for p_dir in DIR_LIST:
        shutil.copytree(p_dir, os.path.join(package_name, p_dir))

    # Create distribution package

    d_package_path = '%s-%s.zip' % (package_name, release)
    d_package = zipfile.ZipFile(d_package_path, 'w', zipfile.ZIP_DEFLATED)

    # If this is the production package, add the combined file
    if has_combined:
        d_package.write(os.path.join(package_name, PLUGIN_METADATA_FILE))
    # Append root files
    for p_file in FILE_LIST:
        d_package.write(os.path.join(package_name, p_file))
    # Append language directory
    for p_dir in DIR_LIST:
        d_package.write(os.path.join(package_name, p_dir))
        # Append files in that directory
        for p_file in os.listdir(os.path.join(package_name, p_dir)):
            d_package.write(os.path.join(package_name, p_dir, p_file))

    d_package.testzip()

    d_package.comment = 'Official packaging by CodeTRAX'

    d_package.printdir()

    d_package.close()


    # Remove the release dir

    shutil.rmtree(package_name)


print 'Complete'
print
