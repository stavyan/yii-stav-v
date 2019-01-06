/**
 * @api {post} api/user/list 用户列表
 * @apiVersion 0.1.0
 * @apiName UserList
 * @apiGroup User
 * @apiPermission none
 *
 * @apiDescription 用户列表
 *
 * @apiSuccess (success 0) {String} firstname Firstname of the User.
 * @apiSuccess (success 0) {String} lastname  Lastname of the User.
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 0 OK
 *     {
 *       "firstname": "John",
 *       "lastname": "Doe"
 *     }
 *
 * @apiUse CreateUserError
 */

/**
 * @api {post} api/user/del 用户删除
 * @apiVersion 0.1.0
 * @apiName DelUser
 * @apiGroup User
 * @apiPermission none
 *
 * @apiDescription 用户删除
 *
 * @apiParam {String} adminuser Adminuser of the User.
 *
 * @apiSuccess (success 0) {String} firstname Firstname of the User.
 * @apiSuccess (success 0) {String} lastname  Lastname of the User.
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 0 OK
 *     {
 *       "firstname": "John",
 *       "lastname": "Doe"
 *     }
 *
 * @apiUse CreateUserError
 */

/**
 * @api {post} api/user/add 新增用户
 * @apiVersion 0.1.0
 * @apiName AddUser
 * @apiGroup User
 * @apiPermission none
 *
 * @apiDescription 新增用户
 *
 * @apiParam {String} adminuser Adminuser of the User.
 * @apiParam {String} adminpass Adminpass of the User.
 *
 * @apiSuccess (success 0) {String} firstname Firstname of the User.
 * @apiSuccess (success 0) {String} lastname  Lastname of the User.
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 0 OK
 *     {
 *       "firstname": "John",
 *       "lastname": "Doe"
 *     }
 *
 * @apiUse CreateUserError
 */

/**
 * @api {post} api/user/edit 编辑用户信息
 * @apiVersion 0.1.0
 * @apiName EditUserInfo
 * @apiGroup User
 * @apiPermission none
 *
 * @apiDescription 新增用户
 *
 * @apiParam {String} adminemail AdminEmail of the User.
 *
 * @apiSuccess (success 0) {String} firstname Firstname of the User.
 * @apiSuccess (success 0) {String} lastname  Lastname of the User.
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 0 OK
 *     {
 *       "firstname": "John",
 *       "lastname": "Doe"
 *     }
 *
 * @apiUse CreateUserError
 */

/**
 * @apiDefine CreateUserError
 *
 * @apiError (Error 1) UserNotFound The id of the User was not found.
 * @apiError (Error 1) UserNotFound1 The id of the User was not found.
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */