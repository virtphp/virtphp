##
# File activates VirtPHP for BASH
##

# Check to see if a Virtual Environment and ask them to exit
if [ "$VIRTUAL_ENV" ] ; then
    echo "You are currently running a virtualenv session: $VIRTUAL_ENV"
    echo "Please exit this session before starting a virtPHP session."
    return 0
fi

# Check to see if switching virtPHP Environments
if [ "$VIRTPHP_ENV_PATH" ] ; then
    read -p "You are currently in a virtPHP session. Do you want to switch? y/n " yn
    case $yn in
        NO) return 0;;
        No) return 0;;
        n) return 0;;
        no) return 0;;
        N) return 0;;
    esac
fi

# Function to make sure used variables are removed
# before environment is setup
deactivate () {

    if [ "$VIRT_PHP_OLD_VIRTUAL_PATH" ] ; then
        PATH="$VIRT_PHP_OLD_VIRTUAL_PATH"
        export PATH
        unset VIRT_PHP_OLD_VIRTUAL_PATH
    fi

    if [ "$PHP_INI_SCAN_DIR" ] ; then
        PHP_INI_SCAN_DIR=""
        export PHP_INI_SCAN_DIR 
        unset PHP_INI_SCAN_DIR 
    fi

    if [ "$VIRT_PHP_OLD_PS1" ] ; then
        PS1="$VIRT_PHP_OLD_PS1"
        export PS1 
        unset VIRT_PHP_OLD_PS1 
    fi

    if [ "$VIRTPHP_ENV_PATH" ]; then
        unset VIRTPHP_ENV_PATH
    fi

    # This should detect bash and zsh, which have a hash command that must
    # be called to get it to forget past commands.  Without forgetting
    # past commands the $PATH changes we made may not be respected
    if [ -n "$BASH" -o -n "$ZSH_VERSION" ] ; then
        hash -r 2>/dev/null
    fi

}

# Reset variables
deactivate

# Current is set when being written by install script
VIRTPHP_ENV_PATH="__VIRTPHP_ENV_PATH__"
export VIRTPHP_ENV_PATH

# Add current path to the bash PATH
VIRT_PHP_OLD_VIRTUAL_PATH="$PATH"
PATH="$VIRTPHP_ENV_PATH/bin:$PATH"
# Use the following if you want to make dynamic in
# the future
# PATH="$PATH_TO_ENV/__BIN_DIR__:$PATH" 
export PATH

# Create a PHP_INI_SCAN_DIR path
PHP_INI_SCAN_DIR="$VIRTPHP_ENV_PATH/etc/php"
export PHP_INI_SCAN_DIR

# Update the shell prompt
if [ -n "$PS1" ] ; then
    VIRT_PHP_OLD_PS1=$PS1
    PS1="(`basename \"$VIRTPHP_ENV_PATH\"`) $PS1"
    export PS1
fi

# This should detect bash and zsh, which have a hash command that must
# be called to get it to forget past commands.  Without forgetting
# past commands the $PATH changes we made may not be respected
if [ -n "$BASH" -o -n "$ZSH_VERSION" ] ; then
    hash -r 2>/dev/null
fi
