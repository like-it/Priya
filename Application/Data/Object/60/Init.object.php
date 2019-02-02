{
    "module": {
        "name": "Priya\/Application"
    },
    "priya": {
        "dir": {
            "application": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/",
            "data": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/",
            "temp": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/Temp\/",
            "cache": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/Temp\/Cache\/",
            "processor": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/Processor\/",
            "root": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/",
            "module": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Module\/",
            "backup": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/Backup\/",
            "restore": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/Restore\/",
            "update": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/Update\/",
            "public": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Public\/"
        },
        "cache": {
            "config": {
                "+ 1 minute": 60,
                "+ 2 minutes": 120,
                "+ 3 minutes": 180,
                "+ 4 minutes": 240,
                "+ 5 minutes": 300,
                "+ 10 minutes": 600,
                "+ 15 minutes": 900,
                "+ 20 minutes": 1200,
                "+ 25 minutes": 1500,
                "+ 30 minutes": 1800,
                "+ 35 minutes": 2100,
                "+ 40 minutes": 2400,
                "+ 45 minutes": 2700,
                "+ 50 minutes": 3000,
                "+ 55 minutes": 3300,
                "+ 60 minutes": 3600,
                "+ 1 hour": 3600,
                "+ 2 hours": 7200,
                "+ 3 hours": 10800,
                "+ 6 hours": 21600,
                "+ 9 hours": 32400,
                "+ 12 hours": 43200,
                "+ 15 hours": 54000,
                "+ 18 hours": 64800,
                "+ 21 hours": 75600,
                "+ 24 hours": 86400,
                "+ 1 day": 86400,
                "+ 2 days": 172800,
                "+ 3 days": 259200,
                "+ 4 days": 345600,
                "+ 5 days": 432000,
                "+ 6 days": 518400,
                "+ 7 days": 604800,
                "+ 1 week": 604800,
                "+ 2 weeks": 1209600,
                "+ 3 weeks": 1814400,
                "+ 4 weeks": 2419200,
                "+ 1 month": 2518500,
                "+ 2 months": 5037000,
                "+ 3 months": 7555500,
                "+ 4 months": 10074000,
                "+ 5 months": 12592500,
                "+ 6 months": 15111000,
                "+ 9 months": 22666500,
                "+ 12 months": 30222000,
                "+ 1 year": 31536000,
                "+ 2 years": 63072000,
                "+ 3 years": 94608000,
                "+ 4 years": 126144000,
                "+ 5 years": 157680000
            },
            "init": {
                "url": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/Object\/60\/Init.object.php",
                "interval": "+ 1 minute"
            }
        },
        "application": {
            "constant": {
                "DS": "\/",
                "PRIYA": "Priya",
                "ENVIRONMENT": "development",
                "MODULE": "Module",
                "TEMPLATE": "Template",
                "PLUGIN": "Plugin",
                "PAGE": "Page",
                "CACHE": "Cache",
                "DATA": "Data",
                "OBJECT": "Object",
                "BACKUP": "Backup",
                "PROCESSOR": "Processor",
                "RESTORE": "Restore",
                "UPDATE": "Update",
                "VENDOR": "Vendor",
                "TEMP": "Temp",
                "PUBLIC_HTML": "Public",
                "HOST": "Host",
                "CSS": "Css",
                "JAVASCRIPT": "Javascript",
                "CONFIG": "Config.json",
                "CUSTOM": "Custom.json",
                "ROUTE": "Route.json",
                "CREDENTIAL": "Credential.json",
                "URL": "Application",
                "OBJECT_INIT_URL": "Init.object.php",
                "OBJECT_INIT_INTERVAL": "+ 1 minute"
            }
        },
        "environment": "production",
        "bin": "priya",
        "version": "0.3.12",
        "contentType": {
            "txt": "text\/plain",
            "html": "text\/html",
            "css": "text\/css",
            "pcss": "text\/css",
            "js": "application\/javascript",
            "json": "application\/json",
            "ico": "image\/x-icon",
            "png": "image\/png",
            "jpg": "image\/jpeg",
            "gif": "image\/gif",
            "svg": "image\/svg+xml",
            "woff": "application\/font-woff",
            "ttf": "font\/ttf",
            "wav": "audio\/wav",
            "mp3": "audio\/mp3",
            "zip": "application\/zip",
            "rar": "application\/octet-stream"
        },
        "route": {
            "default": [
                "Application.Copyright",
                "Application.Version",
                "Application.License",
                "Application.Bin",
                "Application.Locate",
                "Application.Config",
                "Application.Help",
                "Application.Error",
                "Application.Route",
                "Application.Parser",
                "Application.Cache",
                "Application.Task",
                "Application.Host",
                "Test"
            ],
            "url": "\/mnt\/c\/Library\/Server\/Data\/Route.json",
            "cache": {
                "url": "\/mnt\/c\/Library\/Server\/Vendor\/Priya\/Application\/Data\/Temp\/Cache\/60\/Route.json",
                "interval": "+ 1 minute"
            }
        },
        "bug": {
            "cli": {
                "line": {
                    "ending": 2
                }
            },
            "description": {
                "1": "bug.cli.line.ending: 0: not adding newline character, 1: adding 1 newline character, 2: adding 2 newline characters. Default: 2. In CLI mode while echoing, newlines at the end don't work. Adding extra newlines work."
            }
        },
        "major": 0,
        "minor": 3,
        "patch": 12,
        "built": "2018-11-17 23:38:36",
        "autoload": {
            "Host": "\/mnt\/c\/Library\/Server\/Host\/",
            "Priya": "\/mnt\/c\/Library\/Server\/Vendor\/Priya",
            "Priya\\System": "\/mnt\/c\/Library\/Server\/Vendor\/Priya.System",
            "Priya\\Gui": "\/mnt\/c\/Library\/Server\/Vendor\/Priya.Gui",
            "App": "\/mnt\/c\/Library\/Server\/Vendor\/App",
            "Speak": "\/mnt\/c\/Library\/Server\/Vendor\/Speak",
            "Mount\\A\\App": "\/mnt\/c\/Library\/Server\/Mount\/A\/App",
            "Smarty": "\/mnt\/c\/Library\/Server\/Vendor\/Smarty\/libs",
            "PHPMailer\\PHPMailer": "\/mnt\/c\/Library\/Server\/Vendor\/PHPMailer\/src"
        }
    },
    "dir": {
        "ds": "\/",
        "vendor": "\/mnt\/c\/Library\/Server\/Vendor\/",
        "root": "\/mnt\/c\/Library\/Server\/",
        "data": "\/mnt\/c\/Library\/Server\/Data\/",
        "host": "\/mnt\/c\/Library\/Server\/Host\/",
        "mount": {
            "root": "\/mnt\/c\/Library\/Server\/Mount\/",
            "A": "\/mnt\/c\/Library\/Server\/Mount\/A\/",
            "Temp": "\/mnt\/c\/Library\/Server\/Mount\/Temp\/"
        },
        "public": "\/mnt\/c\/Library\/Server\/Public\/"
    },
    "server": {
        "url": "http:\/\/server.local\/",
        "timeout": 600
    },
    "mail": {
        "host": "smtp.live.com",
        "port": 587,
        "secure": "tls",
        "username": "like-it.cloud@outlook.com",
        "from": {
            "email": "like-it.cloud@outlook.com",
            "name": "like-it.cloud@outlook.com"
        },
        "password": "RaXon111"
    },
    "memory": {
        "id": 1,
        "url": "127.0.0.1",
        "port": 11211,
        "weight": 1
    },
    "public_html": "Public"
}