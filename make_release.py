
# Copyright George Notaras

REL_FILES = [
	'add-meta-tags.php',
	'ChangeLog',
	'LICENSE',
	'NOTICE',
	'README',
	'THANKS',
]

PLUGIN_METADATA_FILE = 'add-meta-tags.php'

import sys
import os
import zipfile
import shutil

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

# Create release dir and move release files inside it
os.mkdir(name)
for p_file in REL_FILES:
	shutil.copy(p_file, os.path.join(name, p_file))


# Create distribution package

d_package_path = '%s-%s.zip' % (name, release)
d_package = zipfile.ZipFile(d_package_path, 'w', zipfile.ZIP_DEFLATED)

for p_file in REL_FILES:
	d_package.write(os.path.join(name, p_file))

d_package.testzip()

d_package.comment = 'Official packaging by CodeTRAX'

d_package.printdir()

d_package.close()


# Remove the release dir

shutil.rmtree(name)

