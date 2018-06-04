<template>
    <div class="authent-password" ref="authentPassword">

            <label class="label" :for="pwdId" >{{label}}</label>
            <span class="input-group-btn">
                <input :id="pwdId" ref="authentPassword"
                       class="authent-pwd form-control"
                       type="password"
                       @input.stop="$changePassword"
                       v-model="value"
                       :placeholder="placeholder"
                       required autocapitalize="off" autocorrect="off"/>
                <button ref="authentReveal" class="btn btn-reveal btn-secondary" type="button"><i class="fa fa-eye"></i></button>
            </span>


    </div>
</template>
<script src="./AuthentPassword.component.js"></script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
    @import "./AuthentPassword.scss";
</style>
