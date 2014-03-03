Exec {
    path => [
        "/usr/local/sbin",
        "/usr/local/bin",
        "/usr/sbin",
        "/usr/bin",
        "/sbin",
        "/bin",
        "/opt/vagrant_ruby/bin",
    ],
}

exec { "Update Apt":
    command => "apt-get update",
}

package { [
        "build-essential",
        "vim",
        "git-core",
        "curl",
        "libcurl3-openssl-dev",
        "php5-cli",
        "php5-dev",
        "php5-curl",
        "php-pear",
        "php5-xdebug"
    ]:
    ensure => "installed",
}

file { "/etc/php5/conf.d/phar.ini":
    ensure => "present",
    content => "phar.readonly = Off\n",
    mode => 644,
}
