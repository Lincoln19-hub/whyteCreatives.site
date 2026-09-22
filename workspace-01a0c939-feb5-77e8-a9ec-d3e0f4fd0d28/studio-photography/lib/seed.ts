import 'dotenv/config';
import { drizzle } from 'drizzle-orm/node-postgres';
import { Pool } from 'pg';
import { sessions, packages, packageFeatures } from './schema';

async function seed() {
  const pool = new Pool({
    connectionString: process.env.DATABASE_URL,
  });
  const db = drizzle(process.env.DATABASE_URL as string);

  console.log('🌱 Seeding database...');

  // Clear existing data
  await db.delete(packageFeatures);
  await db.delete(packages);
  await db.delete(sessions);

  // Create sessions
  const sessionData = [
    {
      name: 'Outdoor Portrait',
      slug: 'outdoor-portrait',
      description: 'Beautiful outdoor portrait sessions in natural light settings. Perfect for individuals, couples, or small groups.',
      category: 'Portrait',
      featured: true,
      displayOrder: 1,
      active: true,
    },
    {
      name: 'Studio Portrait',
      slug: 'studio-portrait',
      description: 'Professional studio portrait sessions with controlled lighting and backdrops. Ideal for headshots and formal portraits.',
      category: 'Portrait',
      featured: true,
      displayOrder: 2,
      active: true,
    },
    {
      name: 'Wedding',
      slug: 'wedding',
      description: 'Comprehensive wedding photography coverage from preparation to reception. Capture every magical moment.',
      category: 'Wedding',
      featured: true,
      displayOrder: 3,
      active: true,
    },
    {
      name: 'Graduation',
      slug: 'graduation',
      description: 'Celebrate your academic achievement with professional graduation photos.',
      category: 'Graduation',
      featured: false,
      displayOrder: 4,
      active: true,
    },
    {
      name: 'Family Portrait',
      slug: 'family-portrait',
      description: 'Timeless family portraits that capture the love and connection of your family.',
      category: 'Family',
      featured: true,
      displayOrder: 5,
      active: true,
    },
    {
      name: 'Corporate Headshots',
      slug: 'corporate-headshots',
      description: 'Professional headshots for LinkedIn, company websites, and business materials.',
      category: 'Corporate',
      featured: false,
      displayOrder: 6,
      active: true,
    },
  ];

  const createdSessions = await db.insert(sessions).values(sessionData).returning();
  console.log(`✅ Created ${createdSessions.length} sessions`);

  const sessionMap = new Map(createdSessions.map((s) => [s.slug, s.id]));

  // Create packages
  const packageData = [
    // Outdoor Portrait
    {
      sessionId: sessionMap.get('outdoor-portrait')!,
      name: 'Basic',
      description: 'Perfect for a quick outdoor shoot with natural light.',
      price: '300',
      duration: '1 hour',
      maxPeople: 1,
      editedPhotos: 10,
      outfitChanges: 1,
      locations: 1,
      deliveryTime: '5 Days',
      onlineGallery: true,
      depositPercentage: 50,
      displayOrder: 1,
      active: true,
      features: ['1 Outfit', '1 Location', '10 Edited Images', 'High Resolution Delivery', 'Online Gallery'],
    },
    {
      sessionId: sessionMap.get('outdoor-portrait')!,
      name: 'Standard',
      description: 'Extended outdoor session with more variety.',
      price: '500',
      duration: '2 hours',
      maxPeople: 2,
      editedPhotos: 25,
      outfitChanges: 2,
      locations: 2,
      deliveryTime: '5 Days',
      onlineGallery: true,
      printing: true,
      depositPercentage: 50,
      displayOrder: 2,
      active: true,
      features: ['2 Outfits', '2 Locations', '25 Edited Images', 'High Resolution', 'Online Gallery', '5 Printed Photos'],
    },
    {
      sessionId: sessionMap.get('outdoor-portrait')!,
      name: 'Premium',
      description: 'The ultimate outdoor photography experience.',
      price: '800',
      duration: '3 hours',
      maxPeople: 4,
      editedPhotos: 50,
      outfitChanges: 3,
      locations: 3,
      deliveryTime: '3 Days',
      onlineGallery: true,
      rawImages: true,
      printing: true,
      transportation: true,
      droneCoverage: true,
      priorityEditing: true,
      depositPercentage: 50,
      displayOrder: 3,
      active: true,
      features: ['3 Outfits', '3 Locations', '50 Edited Images', 'All Raw Images', 'Drone Coverage', 'Priority Editing', 'Transportation'],
    },

    // Studio Portrait
    {
      sessionId: sessionMap.get('studio-portrait')!,
      name: 'Basic',
      description: 'Simple studio session with one backdrop.',
      price: '250',
      duration: '1 hour',
      maxPeople: 1,
      editedPhotos: 8,
      outfitChanges: 1,
      deliveryTime: '5 Days',
      onlineGallery: true,
      depositPercentage: 50,
      displayOrder: 1,
      active: true,
      features: ['1 Outfit', '1 Backdrop', '8 Edited Images', 'High Resolution', 'Online Gallery'],
    },
    {
      sessionId: sessionMap.get('studio-portrait')!,
      name: 'Premium',
      description: 'Full studio experience with professional styling.',
      price: '600',
      duration: '2 hours',
      maxPeople: 2,
      editedPhotos: 30,
      outfitChanges: 3,
      deliveryTime: '3 Days',
      onlineGallery: true,
      rawImages: true,
      printing: true,
      priorityEditing: true,
      depositPercentage: 50,
      displayOrder: 2,
      active: true,
      features: ['3 Outfits', 'Unlimited Backdrops', '30 Edited Images', 'All Raw Images', '10 Printed Photos', 'Priority Editing'],
    },

    // Wedding
    {
      sessionId: sessionMap.get('wedding')!,
      name: 'Silver',
      description: 'Essential wedding coverage for intimate ceremonies.',
      price: '2000',
      duration: 'Half Day',
      maxPeople: 50,
      editedPhotos: 100,
      deliveryTime: '14 Days',
      onlineGallery: true,
      depositPercentage: 50,
      displayOrder: 1,
      active: true,
      features: ['Half Day Coverage', '1 Photographer', '100 Edited Images', 'Online Gallery', 'Ceremony & Reception'],
    },
    {
      sessionId: sessionMap.get('wedding')!,
      name: 'Gold',
      description: 'Comprehensive wedding photography.',
      price: '3500',
      duration: 'Full Day',
      maxPeople: 150,
      editedPhotos: 250,
      deliveryTime: '14 Days',
      onlineGallery: true,
      printing: true,
      transportation: true,
      depositPercentage: 50,
      displayOrder: 2,
      active: true,
      features: ['Full Day Coverage', '2 Photographers', '250 Edited Images', 'Photo Album', 'Transportation', 'Pre-Wedding Shoot'],
    },
    {
      sessionId: sessionMap.get('wedding')!,
      name: 'Platinum',
      description: 'The ultimate wedding photography package.',
      price: '5500',
      duration: 'Full Day',
      maxPeople: 300,
      editedPhotos: 500,
      deliveryTime: '7 Days',
      onlineGallery: true,
      rawImages: true,
      printing: true,
      transportation: true,
      droneCoverage: true,
      priorityEditing: true,
      depositPercentage: 40,
      displayOrder: 3,
      active: true,
      features: ['Full Day Coverage', '3 Photographers', '500+ Edited Images', 'All Raw Images', 'Premium Album', 'Drone Coverage', 'Same-Day Highlights'],
    },

    // Graduation
    {
      sessionId: sessionMap.get('graduation')!,
      name: 'Basic',
      description: 'Quick graduation photo session.',
      price: '200',
      duration: '1 hour',
      maxPeople: 1,
      editedPhotos: 10,
      deliveryTime: '3 Days',
      onlineGallery: true,
      depositPercentage: 50,
      displayOrder: 1,
      active: true,
      features: ['1 Outfit', '10 Edited Images', 'Cap & Gown Photos', 'Online Gallery'],
    },
    {
      sessionId: sessionMap.get('graduation')!,
      name: 'Premium',
      description: 'Complete graduation photography package.',
      price: '400',
      duration: '2 hours',
      maxPeople: 5,
      editedPhotos: 30,
      outfitChanges: 2,
      locations: 2,
      deliveryTime: '3 Days',
      onlineGallery: true,
      printing: true,
      priorityEditing: true,
      depositPercentage: 50,
      displayOrder: 2,
      active: true,
      features: ['2 Outfits', '2 Locations', '30 Edited Images', 'Family Group Photos', '5 Printed Photos'],
    },
  ];

  let totalPackages = 0;
  let totalFeatures = 0;

  for (const pkg of packageData) {
    const { features, ...pkgValues } = pkg;
    const [created] = await db.insert(packages).values(pkgValues as typeof packages.$inferInsert).returning();
    totalPackages++;

    if (features && features.length > 0) {
      await db.insert(packageFeatures).values(
        features.map((f, i) => ({
          packageId: created.id,
          feature: f,
          displayOrder: i,
        }))
      );
      totalFeatures += features.length;
    }
  }

  console.log(`✅ Created ${totalPackages} packages`);
  console.log(`✅ Created ${totalFeatures} features`);
  console.log('🎉 Seed complete!');

  await pool.end();
}

seed().catch((e) => {
  console.error('Seed failed:', e);
  process.exit(1);
});
