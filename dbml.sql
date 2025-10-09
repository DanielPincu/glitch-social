
Users {
	id integer pk increments unique
	username varchar(50) null unique
	email varchar(255) null unique
	password varchar(255) null
	is_admin boolean null
	is_blocked boolean null
	created_at timestamp
	last_login timestamp
}

Profiles {
	id integer pk increments unique
	user_id integer null unique *>* Users.id
	bio text
	avatar_url varchar(255)
	location varchar(100)
	website varchar(255)
	created_at timestamp
}

Followers {
	id integer pk increments unique
	user_id integer null *>* Users.id
	follower_id integer null *>* Users.id
	created_at timestamp
}

Posts {
	id integer pk increments unique
	user_id integer null *>* Users.id
	content text
	image_path varchar(255)
	shared_post_id integer *>* Posts.id
	pinned boolean null
	visibility enum('public','private','followers')
	created_at timestamp
}

Comments {
	id integer pk increments unique
	post_id integer null *>* Posts.id
	user_id integer null *>* Users.id
	content text null
	created_at timestamp
}

Likes {
	id integer pk increments unique
	user_id integer null *>* Users.id
	post_id integer null *>* Posts.id
	created_at timestamp
}

Messages {
	id integer pk increments unique
	sender_id integer null *>* Users.id
	receiver_id integer null *>* Users.id
	content text null
	created_at timestamp
}

Notifications {
	id integer pk increments unique
	user_id integer null *>* Users.id
	actor_id integer null *>* Users.id
	type enum('like','comment','follow','mention','share','message') null
	post_id integer *>* Posts.id
	message_id integer *>* Messages.id
	is_read boolean null
	created_at timestamp
}

Post_Views {
	id integer pk increments unique
	post_id integer null *>* Posts.id
	user_id integer *>* Users.id
	created_at timestamp
}

