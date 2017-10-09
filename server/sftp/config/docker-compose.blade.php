version: '3'
services:

  sftp:
    image: atmoz/sftp
    container_name: sp-sftp
    restart: always
    volumes:
{{ $sftpAppVolumes }}
        - ./users.conf:/etc/sftp/users.conf:ro
    ports:
        - "2222:22"

networks:
  default:
    external:
      name: serverpilot
