# XiaoMusic

## Product Introduction

XiaoMusic is an open-source project that uses Xiaomi speakers to play music. Music is downloaded using yt-dlp, enabling unlimited music playback and liberating Xiaomi speakers.

## Key Features

- **Unlimited Music**: Download music through yt-dlp for unlimited playback
- **Xiaomi Speaker Control**: Support voice commands to control music playback
- **Web Management Interface**: Provide a user-friendly web interface for configuration and management
- **Multi-platform Support**: Support Docker deployment, compatible with major NAS platforms
- **Custom Voice Commands**: Fully customizable voice commands with plugin extension support

### Voice Command Features

- **Play Song**: Play local songs
- **Play Song + Song Name**: For example: Play Song Jay Chou Sunny Day
- **Previous Song**: Switch to the previous song
- **Next Song**: Switch to the next song
- **Single Loop**: Loop the current song
- **All Loop**: Loop the entire playlist
- **Random Play**: Play songs randomly
- **Shutdown/Stop Playback**: Stop music playback
- **Refresh List**: Refresh the playlist when songs are copied to the music directory
- **Play List + List Name**: For example: Play List Others
- **Add to Favorites**: Add the currently playing song to the favorites playlist
- **Remove from Favorites**: Remove the currently playing song from the favorites playlist
- **Play Favorites List**: Play the favorites playlist
- **Play Local Song + Song Name**: Won't download if not found locally
- **Play List Number + List Name**: Play a specific song in the playlist
- **Search Play + Keywords**: Search keywords as a temporary search list for playback
- **Local Search Play + Keywords**: Won't download if not found locally

### Important Notes

- For initial configuration, you need to enter your Xiaomi account and password on the page and save it to get the device list
- Music files will be automatically downloaded to the music directory
- Configuration files are saved in the conf directory
- If you encounter problems, you can click the [Download Log File] button at the bottom of the web settings page, then search the log file content to ensure there is no account password information, and attach the downloaded log file when reporting issues
