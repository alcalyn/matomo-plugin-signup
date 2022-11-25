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
            v-model="theSite.name"
            maxlength="90"
            :placeholder="translate('General_Name')"
          />
          <label>{{ translate('General_Name') }}</label>
        </div>
        <div class="col s12 m6"></div>
      </div>

      <div class="editingSiteFooter">
        <input
          v-show="!isLoading"
          type="submit"
          class="btn btn-block btn-large"
          :value="translate('General_Save')"
          @click="saveSite()"
        />
        <p id="nav">
          <a
            href="#"
            @click="cancelEditSite(site)"
          >{{ translate('General_Cancel', '', '') }}</a>
        </p>
      </div>

    </div>
  </div>
</template>

<script lang="ts">
import { DeepReadonly, defineComponent } from 'vue';
import {
  Site,
  MatomoUrl,
  translate,
  AjaxHelper,
  NotificationsStore,
} from 'CoreHome';
import {
  Field,
  Setting,
} from 'CorePluginsAdmin';
import CurrencyStore from '../../../SitesManager/vue/src/CurrencyStore/CurrencyStore';
import SiteTypesStore from '../../../SitesManager/vue/src/SiteTypesStore/SiteTypesStore';
import SiteType from '../../../SitesManager/vue/src/SiteTypesStore/SiteType';

interface SiteFieldsState {
  isLoading: boolean;
  theSite: Site;
  settingValues: Record<string, unknown>;
  showRemoveDialog: boolean;
}

interface CreateEditSiteResponse {
  value: string|number;
}

export default defineComponent({
  data(): SiteFieldsState {
    return {
      isLoading: false,
      theSite: {} as Site,
      settingValues: {},
      showRemoveDialog: false,
    };
  },
  components: {
    Field,
  },
  emits: ['delete', 'editSite', 'cancelEditSite', 'save'],
  created() {
    this.onSiteChanged();
  },
  watch: {
    site() {
      this.onSiteChanged();
    },
  },
  methods: {
    onSiteChanged() {
      const site = this.site as Site;

      this.theSite = { ...site };

      this.editSite();
    },
    editSite() {
      this.$emit('editSite', { idSite: this.theSite.idsite });
    },
    saveSite() {
      const values: Record<string, unknown> = {
        siteName: this.theSite.name,
        currency: this.theSite.currency,
        type: this.theSite.type,
        settingValues: {} as Record<string, Setting[]>,
      };

      // process measurable settings
      Object.entries(this.settingValues).forEach(([fullName, fieldValue]) => {
        const [pluginName, name] = fullName.split('.');

        const settingValues = values.settingValues as Record<string, Setting[]>;
        if (!settingValues[pluginName]) {
          settingValues[pluginName] = [];
        }

        let value = fieldValue;
        if (fieldValue === false) {
          value = '0';
        } else if (fieldValue === true) {
          value = '1';
        } else if (Array.isArray(fieldValue)) {
          value = fieldValue.filter((x) => !!x);
        }

        settingValues[pluginName].push({
          name,
          value,
        });
      });

      AjaxHelper.post<CreateEditSiteResponse>(
        {
          method: 'Signup.signupSite',
        },
        values,
      ).then((response) => {
        if (!this.theSite.idsite && response && response.value) {
          this.theSite.idsite = `${response.value}`;
        }

        if (this.theSite.currency) {
          this.theSite.currency_name = CurrencyStore.currencies.value[this.theSite.currency];
        }

        const notificationId = NotificationsStore.show({
          message: translate('SitesManager_WebsiteCreated'),
          context: 'success',
          id: 'websitecreated',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(notificationId);

        SiteTypesStore.removeEditSiteIdParameterFromHash();

        this.$emit('save', { site: this.theSite, settingValues: values.settingValues, isNew: true });

        document.dispatchEvent(new Event('signup_site_created'));
      });
    },
    cancelEditSite(site: Site) {
      SiteTypesStore.removeEditSiteIdParameterFromHash();

      this.$emit('cancelEditSite', { site, element: this.$refs.root as HTMLElement });

      /* eslint-disable-next-line no-alert */
      if (window.confirm(translate('Signup_CancelSiteConfirm'))) {
        document.dispatchEvent(new Event('signup_site_created'));
      }
    },
  },
  computed: {
    availableTypes() {
      return SiteTypesStore.types.value;
    },
    setupUrl() {
      const site = this.theSite as Site;

      let suffix = '';
      let connector = '';
      if (this.isInternalSetupUrl) {
        suffix = MatomoUrl.stringify({
          idSite: site.idsite,
          period: MatomoUrl.parsed.value.period,
          date: MatomoUrl.parsed.value.date,
          updated: 'false',
        });

        connector = this.howToSetupUrl!.indexOf('?') === -1 ? '?' : '&';
      }
      return `${this.howToSetupUrl}${connector}${suffix}`;
    },
    currencies() {
      return CurrencyStore.currencies.value;
    },
    currentType(): DeepReadonly<SiteType> {
      const site = this.site as Site;
      const type = SiteTypesStore.typesById.value[site.type];
      if (!type) {
        return { name: site.type } as SiteType;
      }
      return type;
    },
    howToSetupUrl() {
      const type = this.currentType;
      if (!type) {
        return undefined;
      }

      return type.howToSetupUrl;
    },
    isInternalSetupUrl() {
      const { howToSetupUrl } = this;
      if (!howToSetupUrl) {
        return false;
      }

      return (`${howToSetupUrl}`).substring(0, 1) === '?';
    },
  },
});
</script>
