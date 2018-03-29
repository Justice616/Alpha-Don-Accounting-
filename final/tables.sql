drop database if exists `journal`;
create database `journal`;

use `journal`;

create table users (
	username varchar(63) unique not null,
	email varchar(255) not null,
	password varchar(255) not null,
	type int not null default 0,

	status int not null default 0,

	primary key (username)
);

create table accounts (
	accno int auto_increment,
	accname varchar(255) not null,
	category int not null default 1,
	username_added varchar(63) not null,
	time_added datetime not null,
	username_modified varchar(63) default null,
	time_modified datetime default null,

	initial_debit float not null default 0,
	initial_credit float not null default 0,

	accstatus int not null default 0,

	primary key (accno),
	foreign key (username_added) references users(username),
	foreign key (username_modified) references users(username)
);

create table profile (
	type int not null,
	description varchar(255) not null,
	can_add bool default 0,
	can_delete bool default 0,
	can_edit bool default 0,
	can_view bool default 0,
	can_search bool default 0
);

create table journal (
	ref int auto_increment,
	username varchar(63) not null,
	entry_date date not null,
	acc1 int not null,
	acc2 int not null,
	acc3 int not null,
	acc4 int not null,
	acc5 int not null,
	description text,
	files text,
	status int default 0,
	response text,

	primary key (ref),
	foreign key (username) references users(username)
);

insert into profile values (0, 'User',          0, 0, 0, 1, 1);
insert into profile values (1, 'Manager',       0, 0, 1, 1, 1);
insert into profile values (2, 'Administrator', 1, 1, 1, 1, 1);

-- dummy values below

insert into users values ('user1', 'user1@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 0, 0);
insert into users values ('user2', 'user2@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 0, 0);
insert into users values ('manager1', 'manager1@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 1, 0);
insert into users values ('manager2', 'manager2@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 1, 0);
insert into users values ('admin1', 'admin1@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 2, 0);
