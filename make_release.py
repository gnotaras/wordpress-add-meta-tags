
# Copyright George Notaras

REL_FILES = [
    'add-meta-tags.pot',
	'add-meta-tags.php',
    'amt-admin-panel.php',
    'amt-settings.php',
    'amt-template-tags.php',
    'amt-utils.php',
    'AUTHORS',
    'CONTRIBUTORS',
	'LICENSE',
	'NOTICE',
	'README',
    'readme.txt',
    'screenshot-1.png',
    'screenshot-2.png',
    'screenshot-3.png',
    'uninstall.php',
]

REL_DIRS = [
    'metadata',
    'languages',
    'css',
    'js',
]

PLUGIN_METADATA_FILE = 'add-meta-tags.php'

POT_HEADER = """#  POT (Portable Object Template)
#
#  This file is part of the Add-Meta-Tags plugin for WordPress.
#
#  http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
#
#  Copyright (C) 2006-2013 George Notaras <gnot@g-loaded.eu>
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

print 'Creating distribution package...'
# Create release dir and move release files inside it
os.mkdir(name)
# Copy files
for p_file in REL_FILES:
	shutil.copy(p_file, os.path.join(name, p_file))
# Copy dirs
for p_dir in REL_DIRS:
    shutil.copytree(p_dir, os.path.join(name, p_dir))

# Create distribution package

d_package_path = '%s-%s.zip' % (name, release)
d_package = zipfile.ZipFile(d_package_path, 'w', zipfile.ZIP_DEFLATED)

# Append root files
for p_file in REL_FILES:
	d_package.write(os.path.join(name, p_file))
# Append language directory
for p_dir in REL_DIRS:
    d_package.write(os.path.join(name, p_dir))
    # Append files in that directory
    for p_file in os.listdir(os.path.join(name, p_dir)):
        d_package.write(os.path.join(name, p_dir, p_file))

d_package.testzip()

d_package.comment = 'Official packaging by CodeTRAX'

d_package.printdir()

d_package.close()


# Remove the release dir

shutil.rmtree(name)

print 'Complete'
print
