<template>
    <div class="devel-center-error-page">
        <div class="devel-center-error-logo">
            <h1 class="devel-center-error-title">Page not found</h1>
        </div>
        <div class="devel-center-error-text">
            The page you're looking for can't be found. Check the URL and try again.
        </div>
    </div>
</template>

<script>
  export default {
    name: "Error"
  }
</script>

<style scoped lang="scss">
    .devel-center-error-page {
        display: flex;
        flex: 1;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #666666;
        font-size: 2rem;

        .devel-center-error-title {

        }

        .devel-center-error-logo {
            display: flex;
            align-items: center;
            font-size: 3rem;
            .devel-center-error-logo--icon {
                margin-left: .5rem;
                .material-icons {
                    font-size: inherit;
                }
            }
        }
    }
</style>