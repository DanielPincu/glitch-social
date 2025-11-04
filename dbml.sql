Table users {
  id int [pk, increment]
  username varchar(50) [not null, unique]
  email varchar(255) [not null, unique]
  password varchar(255) [not null]
  is_admin boolean [not null, default: 0]
  is_blocked boolean [not null, default: 0]
  reset_token varchar(255)
  reset_expires datetime
  created_at timestamp [default: `current_timestamp`]
}

Table profiles {
  id int [pk, increment]
  user_id int [not null, unique, ref: > users.id]
  bio text
  avatar_url varchar(255)
  location varchar(100)
  website varchar(255)
  created_at timestamp [default: `current_timestamp`]
}

Table followers {
  id int [pk, increment]
  user_id int [not null, ref: > users.id]
  follower_id int [not null, ref: > users.id]
  created_at timestamp [default: `current_timestamp`]
}

Table posts {
  id int [pk, increment]
  user_id int [not null, ref: > users.id]
  content text
  image_path varchar(255)
  visibility enum('public', 'private', 'followers') [default: 'public']
  is_pinned boolean [not null, default: 0]
  created_at timestamp [default: `current_timestamp`]
}

Table comments {
  id int [pk, increment]
  post_id int [not null, ref: > posts.id]
  user_id int [not null, ref: > users.id]
  content text [not null]
  created_at timestamp [default: `current_timestamp`]
}

Table likes {
  id int [pk, increment]
  user_id int [not null, ref: > users.id]
  post_id int [not null, ref: > posts.id]
  created_at timestamp [default: `current_timestamp`]
}

Table blocked_users {
  id int [pk, increment]
  blocker_id int [not null, ref: > users.id]
  blocked_id int [not null, ref: > users.id]
}

Table notifications {
  id int [pk, increment]
  user_id int [not null, ref: > users.id] // recipient
  actor_id int [not null, ref: > users.id] // actor
  post_id int [ref: > posts.id]
  type enum('post', 'follow') [default: 'post']
  created_at timestamp [default: `current_timestamp`]
}

Table zion_messages {
  id int [pk, increment]
  user_id int [not null, ref: > users.id]
  content text [not null]
  created_at timestamp [default: `current_timestamp`]
}
