<template>
  <Html :lang="locale">
    <Head>
      <!-- Global Meta tags, Favicons, and SEO defaults -->
      <Title>{{ title }}</Title>
      <Meta name="description" content="Smart ZeroWaste meal planning and recipe optimization." />
    </Head>
    <Body class="bg-background text-on-background font-body-md antialiased">
      
      <!-- NuxtLayout handles switching between App Layouts (e.g., Dashboard Layout vs Guest Layout) -->
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>

      <!-- Global Notification Toasts (e.g., "Recipe saved!", "Error connecting to server") -->
      <ClientOnly>
        <NotificationToastContainer />
      </ClientOnly>

    </Body>
  </Html>
</template>

<script setup>
// Global setup logic, like initializing theme preferences or global user session checks
const locale = ref('en')
const title = useState('app-title', () => 'LeKellFogyni - Smart & ZeroWaste')

// Example of a global app-level lifecycle hook
onMounted(() => {
  console.log('Application shell mounted globally.')
})
</script>