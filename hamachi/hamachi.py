#!/usr/bin/env python

import subprocess
p = subprocess.Popen(['hamachi'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
out, err = p.communicate()
print """
<html>
<head>
<title></title>
</head>
<body>
"""
for line in out.split("\n"):
    print "<br>"+line
print "<br>If the service it's not running just ignore the init thing and use the web buttons to enable/disable the hamachi app."
print """
</body>
</html>
"""
