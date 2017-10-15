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
    ports:
        - "2222:22"
    {{ ! sp_is_windows() ? "
    logging:
        driver: syslog" : "" }}

networks:
  default:
    external:
      name: serverpilot
