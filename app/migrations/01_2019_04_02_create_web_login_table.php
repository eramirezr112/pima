CREATE TABLE web_log_session (
	[num_log] [int] NOT NULL IDENTITY PRIMARY KEY,
	[cod_usuario] [int] NOT NULL ,
	[login_token] [varchar] (50) COLLATE Modern_Spanish_CI_AS NOT NULL ,
	[login_date]  [datetime] NOT NULL ,
	[login_time]  [varchar] (10) COLLATE Modern_Spanish_CI_AS NOT NULL ,
	[login_status][varchar] (2) COLLATE Modern_Spanish_CI_AS NOT NULL
)