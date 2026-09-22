import {
  pgTable,
  serial,
  varchar,
  text,
  integer,
  boolean,
  numeric,
  timestamp,
} from "drizzle-orm/pg-core";

// ── Sessions ──────────────────────────────────────────────────────────────────
export const sessions = pgTable("sessions", {
  id: serial("id").primaryKey(),
  name: varchar("name", { length: 255 }).notNull(),
  slug: varchar("slug", { length: 255 }).notNull().unique(),
  description: text("description").default(""),
  category: varchar("category", { length: 255 }).default(""),
  image: text("image").default(""),
  featured: boolean("featured").default(false).notNull(),
  displayOrder: integer("display_order").default(0).notNull(),
  active: boolean("active").default(true).notNull(),
  deletedAt: timestamp("deleted_at"),
  createdAt: timestamp("created_at").defaultNow().notNull(),
  updatedAt: timestamp("updated_at").defaultNow().notNull(),
});


// ── Packages ──────────────────────────────────────────────────────────────────
export const packages = pgTable("packages", {
  id: serial("id").primaryKey(),
  sessionId: integer("session_id")
    .notNull()
    .references(() => sessions.id, { onDelete: "restrict" }),
  name: varchar("name", { length: 255 }).notNull(),
  description: text("description").default(""),
  price: numeric("price", { precision: 10, scale: 2 }).notNull(),
  duration: varchar("duration", { length: 100 }).notNull(),
  maxPeople: integer("max_people").default(1),
  editedPhotos: integer("edited_photos").default(0),
  outfitChanges: integer("outfit_changes").default(1),
  locations: integer("locations").default(1),
  deliveryTime: varchar("delivery_time", { length: 100 }).default("3 Days"),
  onlineGallery: boolean("online_gallery").default(false).notNull(),
  rawImages: boolean("raw_images").default(false).notNull(),
  printing: boolean("printing").default(false).notNull(),
  transportation: boolean("transportation").default(false).notNull(),
  droneCoverage: boolean("drone_coverage").default(false).notNull(),
  priorityEditing: boolean("priority_editing").default(false).notNull(),
  depositPercentage: integer("deposit_percentage").default(50).notNull(),
  rescheduleAllowed: boolean("reschedule_allowed").default(true).notNull(),
  rescheduleHours: integer("reschedule_hours").default(48),
  displayOrder: integer("display_order").default(0).notNull(),
  active: boolean("active").default(true).notNull(),
  deletedAt: timestamp("deleted_at"),
  createdAt: timestamp("created_at").defaultNow().notNull(),
  updatedAt: timestamp("updated_at").defaultNow().notNull(),
});


// ── Package Features ──────────────────────────────────────────────────────────
export const packageFeatures = pgTable("package_features", {
  id: serial("id").primaryKey(),
  packageId: integer("package_id")
    .notNull()
    .references(() => packages.id, { onDelete: "cascade" }),
  feature: varchar("feature", { length: 255 }).notNull(),
  displayOrder: integer("display_order").default(0).notNull(),
});


// ── Bookings (client bookings with Paystack deposit) ──────────────────────────
export const bookings = pgTable("bookings", {
  id: serial("id").primaryKey(),
  sessionId: integer("session_id"),
  sessionName: varchar("session_name", { length: 255 }).notNull(),
  packageId: integer("package_id"),
  packageName: varchar("package_name", { length: 255 }).notNull(),
  clientName: varchar("client_name", { length: 255 }).notNull(),
  clientEmail: varchar("client_email", { length: 255 }).notNull(),
  clientPhone: varchar("client_phone", { length: 100 }).default(""),
  eventDate: varchar("event_date", { length: 20 }).default(""),
  eventLocation: varchar("event_location", { length: 255 }).default(""),
  deliveryDate: varchar("delivery_date", { length: 20 }).default(""),
  basePrice: numeric("base_price", { precision: 12, scale: 2 }).notNull(),
  surchargePct: integer("surcharge_pct").default(0).notNull(),
  surchargeAmount: numeric("surcharge_amount", { precision: 12, scale: 2 }).default("0").notNull(),
  total: numeric("total", { precision: 12, scale: 2 }).notNull(),
  deposit: numeric("deposit", { precision: 12, scale: 2 }).notNull(),
  depositPercentage: integer("deposit_percentage").default(50).notNull(),
  status: varchar("status", { length: 30 }).default("pending").notNull(), // pending | confirmed | completed | cancelled
  paystackRef: varchar("paystack_ref", { length: 120 }).default(""),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

// ── Invoices ──────────────────────────────────────────────────────────────────
export const invoices = pgTable("invoices", {
  id: serial("id").primaryKey(),
  number: varchar("number", { length: 120 }).notNull(),
  bookingId: integer("booking_id"),
  galleryId: integer("gallery_id"),
  purpose: varchar("purpose", { length: 40 }).default("booking_deposit").notNull(), // booking_deposit | gallery_balance
  clientName: varchar("client_name", { length: 255 }).notNull(),
  clientEmail: varchar("client_email", { length: 255 }).default(""),
  total: numeric("total", { precision: 12, scale: 2 }).notNull(),
  status: varchar("status", { length: 20 }).default("unpaid").notNull(), // unpaid | paid
  paystackRef: varchar("paystack_ref", { length: 120 }).default(""),
  dueDate: varchar("due_date", { length: 20 }).default(""),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

// ── Client Galleries (Google Drive powered delivery portals) ──────────────────
export const galleries = pgTable("galleries", {
  id: serial("id").primaryKey(),
  slug: varchar("slug", { length: 120 }).notNull().unique(),
  title: varchar("title", { length: 255 }).notNull(),
  clientName: varchar("client_name", { length: 255 }).notNull(),
  clientEmail: varchar("client_email", { length: 255 }).default(""),
  passwordHash: varchar("password_hash", { length: 128 }).default(""), // sha256(app_secret + password)
  gdriveFolder: text("gdrive_folder").default(""),
  coverUrl: text("cover_url").default(""),
  expiryDate: varchar("expiry_date", { length: 20 }).default(""), // YYYY-MM-DD, empty = never
  balance: numeric("balance", { precision: 12, scale: 2 }).default("0").notNull(),
  status: varchar("status", { length: 20 }).default("unpaid").notNull(), // unpaid | paid
  paystackRef: varchar("paystack_ref", { length: 120 }).default(""),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

// ── Download Events (live notifications feed) ────────────────────────────────
export const downloadEvents = pgTable("download_events", {
  id: serial("id").primaryKey(),
  galleryId: integer("gallery_id").notNull(),
  type: varchar("type", { length: 20 }).default("single").notNull(), // single | zip
  item: varchar("item", { length: 255 }).default(""),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

// ── Studio Settings (key-value store) ────────────────────────────────────────
export const settings = pgTable("settings", {
  key: varchar("key", { length: 100 }).primaryKey(),
  value: text("value").default(""),
  updatedAt: timestamp("updated_at").defaultNow().notNull(),
});
