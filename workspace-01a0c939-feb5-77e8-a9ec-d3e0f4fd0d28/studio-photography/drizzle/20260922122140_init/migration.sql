CREATE TABLE "bookings" (
	"id" serial PRIMARY KEY,
	"session_id" integer,
	"session_name" varchar(255) NOT NULL,
	"package_id" integer,
	"package_name" varchar(255) NOT NULL,
	"client_name" varchar(255) NOT NULL,
	"client_email" varchar(255) NOT NULL,
	"client_phone" varchar(100) DEFAULT '',
	"event_date" varchar(20) DEFAULT '',
	"event_location" varchar(255) DEFAULT '',
	"delivery_date" varchar(20) DEFAULT '',
	"base_price" numeric(12,2) NOT NULL,
	"surcharge_pct" integer DEFAULT 0 NOT NULL,
	"surcharge_amount" numeric(12,2) DEFAULT '0' NOT NULL,
	"total" numeric(12,2) NOT NULL,
	"deposit" numeric(12,2) NOT NULL,
	"deposit_percentage" integer DEFAULT 50 NOT NULL,
	"status" varchar(30) DEFAULT 'pending' NOT NULL,
	"paystack_ref" varchar(120) DEFAULT '',
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "download_events" (
	"id" serial PRIMARY KEY,
	"gallery_id" integer NOT NULL,
	"type" varchar(20) DEFAULT 'single' NOT NULL,
	"item" varchar(255) DEFAULT '',
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "galleries" (
	"id" serial PRIMARY KEY,
	"slug" varchar(120) NOT NULL UNIQUE,
	"title" varchar(255) NOT NULL,
	"client_name" varchar(255) NOT NULL,
	"client_email" varchar(255) DEFAULT '',
	"password_hash" varchar(128) DEFAULT '',
	"gdrive_folder" text DEFAULT '',
	"cover_url" text DEFAULT '',
	"expiry_date" varchar(20) DEFAULT '',
	"balance" numeric(12,2) DEFAULT '0' NOT NULL,
	"status" varchar(20) DEFAULT 'unpaid' NOT NULL,
	"paystack_ref" varchar(120) DEFAULT '',
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "invoices" (
	"id" serial PRIMARY KEY,
	"number" varchar(120) NOT NULL,
	"booking_id" integer,
	"gallery_id" integer,
	"purpose" varchar(40) DEFAULT 'booking_deposit' NOT NULL,
	"client_name" varchar(255) NOT NULL,
	"client_email" varchar(255) DEFAULT '',
	"total" numeric(12,2) NOT NULL,
	"status" varchar(20) DEFAULT 'unpaid' NOT NULL,
	"paystack_ref" varchar(120) DEFAULT '',
	"due_date" varchar(20) DEFAULT '',
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "package_features" (
	"id" serial PRIMARY KEY,
	"package_id" integer NOT NULL,
	"feature" varchar(255) NOT NULL,
	"display_order" integer DEFAULT 0 NOT NULL
);
--> statement-breakpoint
CREATE TABLE "packages" (
	"id" serial PRIMARY KEY,
	"session_id" integer NOT NULL,
	"name" varchar(255) NOT NULL,
	"description" text DEFAULT '',
	"price" numeric(10,2) NOT NULL,
	"duration" varchar(100) NOT NULL,
	"max_people" integer DEFAULT 1,
	"edited_photos" integer DEFAULT 0,
	"outfit_changes" integer DEFAULT 1,
	"locations" integer DEFAULT 1,
	"delivery_time" varchar(100) DEFAULT '3 Days',
	"online_gallery" boolean DEFAULT false NOT NULL,
	"raw_images" boolean DEFAULT false NOT NULL,
	"printing" boolean DEFAULT false NOT NULL,
	"transportation" boolean DEFAULT false NOT NULL,
	"drone_coverage" boolean DEFAULT false NOT NULL,
	"priority_editing" boolean DEFAULT false NOT NULL,
	"deposit_percentage" integer DEFAULT 50 NOT NULL,
	"reschedule_allowed" boolean DEFAULT true NOT NULL,
	"reschedule_hours" integer DEFAULT 48,
	"display_order" integer DEFAULT 0 NOT NULL,
	"active" boolean DEFAULT true NOT NULL,
	"deleted_at" timestamp,
	"created_at" timestamp DEFAULT now() NOT NULL,
	"updated_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "sessions" (
	"id" serial PRIMARY KEY,
	"name" varchar(255) NOT NULL,
	"slug" varchar(255) NOT NULL UNIQUE,
	"description" text DEFAULT '',
	"category" varchar(255) DEFAULT '',
	"image" text DEFAULT '',
	"featured" boolean DEFAULT false NOT NULL,
	"display_order" integer DEFAULT 0 NOT NULL,
	"active" boolean DEFAULT true NOT NULL,
	"deleted_at" timestamp,
	"created_at" timestamp DEFAULT now() NOT NULL,
	"updated_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
ALTER TABLE "package_features" ADD CONSTRAINT "package_features_package_id_packages_id_fkey" FOREIGN KEY ("package_id") REFERENCES "packages"("id") ON DELETE CASCADE;--> statement-breakpoint
ALTER TABLE "packages" ADD CONSTRAINT "packages_session_id_sessions_id_fkey" FOREIGN KEY ("session_id") REFERENCES "sessions"("id") ON DELETE RESTRICT;