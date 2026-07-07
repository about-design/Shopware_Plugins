(()=>{"use strict";let e=`{% block experience_index %}
    <div class="platform-page container platform-page--experience-index">
        <h1 class="platform-title">{{ getPageTitle() }}</h1>

        <page-loader v-if="isLoading"></page-loader>

        <div v-else-if="loadError" class="alert alert-danger">
            {{ $trans('metaB2bExperienceHub.loadError') }}
        </div>

        <div v-else-if="!displayExperiences.length" class="alert alert-info">
            {{ $trans('metaB2bExperienceHub.emptyState') }}
        </div>

        <template v-else>
            <ul class="nav nav-tabs mb-4" v-if="showTabs">
                <li class="nav-item" v-for="experience in displayExperiences" :key="experience.id">
                    <a
                        class="nav-link"
                        role="tab"
                        :class="{'active': activeExperience && activeExperience.id === experience.id}"
                        href="#"
                        @click.prevent="selectExperience(experience)"
                    >
                        {{ getExperienceTitle(experience) }}
                    </a>
                </li>
            </ul>

            <meta-experience-cms-page
                v-if="activeExperience && activeCmsPageId"
                :key="activeExperience.id"
                :cms-page-id="activeCmsPageId"
                :is-active="true"
            ></meta-experience-cms-page>

            <div v-else-if="activeExperience && !activeCmsPageId" class="alert alert-warning">
                {{ $trans('metaB2bExperienceHub.missingCmsPage') }}
            </div>
        </template>
    </div>
{% endblock %}
`,{Component:i}=B2bPlatform;i.register("experience-index",{template:e,inject:["apiService"],data:()=>({isLoading:!1,experiences:[],activeExperienceId:null,loadError:!1}),computed:{isSingleMenuView(){return!!this.$route.params.assignmentId},displayExperiences(){return this.isSingleMenuView?this.experiences.filter(e=>e.id===this.$route.params.assignmentId):this.experiences},activeExperience(){return this.displayExperiences.length?this.activeExperienceId&&this.displayExperiences.find(e=>e.id===this.activeExperienceId)||this.displayExperiences[0]:null},showTabs(){return!this.isSingleMenuView&&this.displayExperiences.length>1},activeCmsPageId(){return this.getCmsPageId(this.activeExperience)}},watch:{"$route.params.assignmentId":{immediate:!0,handler(e){this.activeExperienceId=e||null,this.loadExperiences()}},activeExperienceId(e){e&&this.trackExperienceView(e)}},methods:{getPageTitle(){return this.isSingleMenuView&&this.activeExperience?this.getExperienceTitle(this.activeExperience):this.$trans("metaB2bExperienceHub.pageTitle")},getExperienceTitle:e=>e?.translated?.title||e?.title||"",getCmsPageId:e=>e?.cmsPageId||e?.cmsPage?.id||null,loadExperiences(){this.isLoading=!0,this.loadError=!1,this.apiService.get("meta-b2b-experiences").then(e=>{let i=e.data||{};return this.experiences=i.elements||[],this.ensureActiveExperienceLoaded()}).then(()=>{this.isLoading=!1,this.activeExperienceId||1!==this.displayExperiences.length||(this.activeExperienceId=this.displayExperiences[0].id),this.$route.params.assignmentId?this.activeExperienceId=this.$route.params.assignmentId:this.activeExperienceId&&this.trackExperienceView(this.activeExperienceId)}).catch(()=>{this.isLoading=!1,this.loadError=!0})},ensureActiveExperienceLoaded(){let e=this.$route.params.assignmentId;return!e||this.experiences.some(i=>i.id===e)?Promise.resolve():this.apiService.get("meta-b2b-experiences/"+e).then(e=>{e.data&&(this.experiences=[e.data])}).catch(()=>{this.loadError=!0})},selectExperience(e){this.activeExperienceId=e.id,this.$router.push("/experiences/"+e.id).catch(()=>{})},trackExperienceView(e){this.apiService.get("meta-b2b-experiences/"+e).catch(()=>{})}}});let{Module:t}=B2bPlatform;t.register("meta_b2b_experiences",{name:"meta_b2b_experiences",routePrefixPath:"experiences",routes:{index:{component:"experience-index",path:null},detail:{component:"experience-index",path:":assignmentId"}}});let a=`{% block meta_experience_cms_page %}
    <div class="cms-page-container meta-experience-cms-page">
        <page-loader v-if="isLoading"></page-loader>

        <div v-else-if="loadError" class="alert alert-danger">
            {{ $trans('metaB2bExperienceHub.cmsLoadError') }}
        </div>

        <component
            class="platform-cms"
            v-bind:is="transformed"
            v-if="!isLoading && cmsPage"
        ></component>
    </div>
{% endblock %}
`,{Component:s,Platform:r}=B2bPlatform;s.register("meta-experience-cms-page",{template:a,inject:["apiService"],props:{cmsPageId:{type:String,required:!0},isActive:{type:Boolean,default:!0}},data:()=>({isLoading:!1,cmsPage:null,loadError:!1}),computed:{transformed(){return{template:this.cmsPage}}},watch:{cmsPageId:{immediate:!0,handler(){this.fetchCmsPage()}},isActive(e){!e||this.cmsPage||this.isLoading||this.fetchCmsPage()}},methods:{fetchCmsPage(){this.cmsPageId&&this.isActive&&(this.isLoading=!0,this.cmsPage=null,this.loadError=!1,this.apiService.customRequest({url:"platform-cms/"+this.cmsPageId,method:"get",baseURL:r.appContext.basePath,headers:{"sw-access-key":r.apiContext.accessKey,"X-Requested-With":"XMLHttpRequest"}}).then(e=>{this.cmsPage=e.data,this.isLoading=!1,window.PluginManager&&window.PluginManager.initializePlugins()}).catch(()=>{this.isLoading=!1,this.loadError=!0}))}}})})();