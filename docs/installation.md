#eep installation

## Installation - Linux
1. Extract to somewhere, like ```$ /home/dfp/eep```
2. Make the controller executable
```sh 
$ chmod +x eep.php
```
3. Create a globally available link, like,
```sh 
$ su
$ cd /usr/bin
$ ln -s -T /home/dfp/eep/eep.php eep
```
4. edit eepSetting.php to update the path to the cache file to somewhere that is writable on your system, like ```$ /var/tmp/```


### Command line completion
On linuxes you can set up bash completion (which is highly convenient) by:
```
$ sudo ln -s /home/dfp/eep/bash_completion/eep /etc/bash_completion.d/eep
```
Note that on CentOS, you might have to:
```sh
$ sudo yum install bash-completion
```

In order to insert the eep commandline completion script into the current bash session, you have to either ```$ . /etc/bash_completion``` or start a new shell (like on CentOS).

## Installation - OS X

### Manual:
1. Create a bash completion directory and symlink it.
```sh
$ sudo mdkir /etc/bash_completion.d
$ sudo ln -s /your_path_to_eep/bash_completion/eep /etc/bash_completion.d/eep
```

2. Then add the following to your ```~/.bash_profile```, ```~/.bashrc``` etc. 
```sh
for f in /etc/bash_completion.d/*; do source $f; done
```
Any file in that location will now be sourced for bash use.
3. Start a new shell.

### Via Homebrew / Mac Ports
1. See [http://superuser.com/questions/288438/bash-completion-for-commands-in-mac-os](http://superuser.com/questions/288438/bash-completion-for-commands-in-mac-os)
2. Then create the symbolic link to ```/your_path_to_eep/bash_completion/eep``` in the installation specific completion folder.
3. Start a new shell

## Installation - Windows
1. Extract to somewhere, like ```C:\wamp\scripts\eep```
2. Make the controller executable: 
```sh
icacls C:\wamp\scripts\eep\eep.php /T /Q /C /RESET
```
3. Create a globally available link by adding eep.php to the ```PATH``` variable

### Installation note
You can override the settings by copying ```.../eep/eepSettings.php``` into your home folder and editing it. You may have to keep it uptodate with new versions, as these settings change.

