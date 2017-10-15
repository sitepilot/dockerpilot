version: '3'
services:

  sftp:
    image: sitepilot/sftp:1.0
    container_name: sp-sftp
    restart: always
    volumes:
{{ $sftpAppVolumes }}
        - ./users.conf:/etc/sftp/users.conf:ro
        {{ ! sp_is_windows() ? "- /var/log/syslog:/var/log/serverpilot_syslog" : "" }}
        {{ ! sp_is_windows() ? "- ".SERVER_WORKDIR."/config/fail2ban/jail.local:/etc/fail2ban/jail.local" : "" }}
        {{ ! sp_is_windows() ? "- ".SERVER_WORKDIR."/config/fail2ban/fail2ban.local:/etc/fail2ban/fail2ban.local" : "" }}
        {{ ! sp_is_windows() ? "- /etc/timezone:/etc/timezone.host:ro" : "" }}
    ports:
        - "2222:22"
    {{ ! sp_is_windows() ? "
    logging:
        driver: syslog" : "" }}

networks:
  default:
    external:
      name: serverpilot
