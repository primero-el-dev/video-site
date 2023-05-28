<template>
  <rating-form 
    :rating="innerEntity" 
    :backendRoute="rateRoute"
  >
    <button 
      slot="additionalButtons" 
      type="button" 
      id="btnGroupDrop1" 
      class="btn btn-outline btn-outline-primary" 
      data-bs-toggle="dropdown" 
      aria-expanded="false"
    >
      <i class="fa fa-bars" aria-hidden="true"></i>
    </button>
    <ul slot="additionalButtons" class="dropdown-menu dropdown-menu-end" aria-labelledby="btnGroupDrop1">
      <li 
        v-if="currentUserDidAction('report')" 
        @click="unreport"
        class="dropdown-item" 
      >
        {{ t('common.unreport') }}
      </li>

      <li 
        v-else 
        @click="openReportModal"
        data-bs-toggle="modal" 
        class="dropdown-item" 
      >
        {{ t('common.report') }}
      </li>

      <slot name="additionalOptions"></slot>
    </ul>

    <div 
      slot="additionalButtons" 
      :id="reportModalId" 
      class="modal fade" 
      tabindex="-1" 
      aria-labelledby="reportModalLabel" 
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reportModalLabel">
              {{ t('common.report') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <custom-form 
              :redirectAfterSuccess="false"
              :submitRoute="reportRoute"
              :submitText="t('common.report')"
              :formControls="[{ name: 'reason', label: t('common.reason'), placeholder: t('common.reason') }]"
              @responseData="onReportResponse"
            ></custom-form>
          </div>
        </div>
      </div>
    </div>
  </rating-form>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import HasRating from '../entity/has-rating';
  import HasUserActions, { addUserAction, deleteUserAction, UserAction } from '../entity/has-user-actions';
  import RatingForm from './forms/rating-form.vue';
  import CustomForm from './custom-form.vue';
  import { BackendRoute } from '../router';
  import { getResponseError, requestApi, SuccessfulApiResponse } from '../api';
  import { useAlertStore } from '../state';
  import { Modal } from 'bootstrap';
  import { v4 as uuidv4 } from 'uuid';

  export default defineComponent({
    emits: ['entityReported', 'entityUnreported', 'entityUpdate', 'entityDelete'],
    components: {
      RatingForm,
      CustomForm,
    },
    props: {
      entity: {
        type: Object as PropType<HasRating & HasUserActions>,
        required: true,
      },
      rateRoute: {
        type: Object as PropType<BackendRoute>,
        required: true,
      },
      reportRoute: {
        type: Object as PropType<BackendRoute>,
        required: true,
      },
      reportDeleteRoute: {
        type: Object as PropType<BackendRoute>,
        required: true,
      },
    },
    data() {
      return {
        innerEntity: this.entity as (HasRating & HasUserActions),
        reportModal: null as Modal | null,
        reportModalId: 'reportModal-' + uuidv4(),
      }
    },
    watch: {
      entity: {
        handler(newValue: (HasRating & HasUserActions)): void {
          this.innerEntity = newValue;
        },
        deep: true,
      },
    },
    methods: {
      t(key: string): string {
        return this.$t(key) as string;
      },
      openReportModal(): void {
        this.reportModal = this.reportModal || new Modal(document.getElementById(this.reportModalId)!);
        this.reportModal.show();
      },
      onReportResponse(response: SuccessfulApiResponse): void {
        addUserAction(this.innerEntity, response.data! as unknown as UserAction);
        alert(response.message!);
        this.$emit('entityReported', response);
        
        if (this.reportModal) {
          this.reportModal.hide();
        }
      },
      unreport(): void {
        requestApi(this.reportDeleteRoute)
          .then(response => {
            deleteUserAction(this.innerEntity, 'report');
            alert(response.data.message);
            this.$emit('entityUnreported', response);
          })
          .catch(e => {
            useAlertStore().addDanger(getResponseError(e));
          });
      },
      currentUserDidAction(action: string): boolean {
        return this.innerEntity.currentUserActions!.filter(a => a.action === action).length > 0;
      },
    },
  });
</script>