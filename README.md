# Video site

# Website for sharing videos

## Features
- Login, registration, reset password (currently without sending emails, but works as they were enabled, that means tokens are inserted to database)
- Add, delete and replace videos
- Comment videos and other comments
- Rate and report comments or videos
- Real-time notifications via websockets (in progress)
- Making snapshot and sample from video before upload 

## Technologies
- Symfony 5/6 REST API with couple of standard HTTP endpoints for files upload (there are packages in both, forced to use one or couple in 5.4 version because of Ratchet errors)
- Vue 2 as SPA
- Ratchet (websockets)
- Bootstrap 5

