<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  Copied and adapted from:

    plugins/SitesManager/vue/src/SiteFields/SiteFields.vue

-->

<template>
  <div
    class="site card hoverable editingSite"
    ref="root"
  >
    <div class="card-content">

      <h2 class="card-title">{{ translate('SitesManager_AddMeasurable') }}</h2>

      <div class="form-group row">
        <div class="col s12 m6 input-field">
          <input
            type="text"
            v-model="site.name"
            maxlength="90"
            :placeholder="translate('General_Name')"
          />
          <label>{{ translate('General_Name') }}</label>
        </div>
        <div class="col s12 m6"></div>
      </div>

      <div class="editingSiteFooter">
        <input
          type="submit"
          class="btn btn-block btn-large"
          :value="translate('General_Save')"
          @click="saveSite()"
        />
        <p id="nav">
          <a
            href="#"
            @click="cancelEditSite()"
          >{{ translate('General_Cancel', '', '') }}</a>
        </p>
      </div>

    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Site,
  translate,
  AjaxHelper,
  NotificationsStore,
} from 'CoreHome';

interface SiteFieldsState {
  site: Site;
}

interface CreateEditSiteResponse {
  value: string|number;
}

export default defineComponent({
  data(): SiteFieldsState {
    return {
      site: {} as Site,
    };
  },
  methods: {
    saveSite() {
      AjaxHelper.post<CreateEditSiteResponse>(
        {
          method: 'Signup.signupSite',
        },
        {
          siteName: this.site.name,
        },
      ).then(() => {
        const notificationId = NotificationsStore.show({
          message: translate('SitesManager_WebsiteCreated'),
          context: 'success',
          id: 'websitecreated',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(notificationId);

        document.dispatchEvent(new Event('signup_site_created'));
      });
    },
    cancelEditSite() {
      /* eslint-disable-next-line no-alert */
      if (window.confirm(translate('Signup_CancelSiteConfirm'))) {
        document.dispatchEvent(new Event('signup_site_created'));
      }
    },
  },
});
</script>
