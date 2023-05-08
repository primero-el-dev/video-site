<template>
  <div :class="'snapshot-container mb-3 bg-' + background">
    <canvas slot="content" class="snapshot" :id="id"></canvas>
  </div>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import { Color } from '../common';

  export default defineComponent({
    props: {
      background: {
        type: String as PropType<Color>,
        required: false,
        default: 'transparent',
      },
      id: {
        type: String,
        required: true,
      },
    },
    computed: {
      getContext(): CanvasRenderingContext2D {
        let context = (document.getElementById(this.id) as HTMLCanvasElement).getContext('2d');
        if (!context) {
          throw `Cannot get canvas context of element with id ${this.id}.`;
        }
        return context;
      },
    },
  });
</script>