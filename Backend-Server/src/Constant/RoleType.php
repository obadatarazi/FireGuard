<?php

namespace App\Constant;

enum RoleType: string
{

    public function getSection(): string
    {
        $slices = explode(',', $this->value);
        return $slices[0];
    }

    public function getName(): string
    {
        $slices = explode(',', $this->value);
        return str_replace('_', ' ', $slices[1]);
    }

    public function getDesc(): string
    {
        $slices = explode(',', $this->value);
        return str_replace('_', ' ', $slices[1]);
    }

    public function getRole(): string
    {
        $slices = explode(',', $this->value);
        return $slices[1];
    }

    case AccessToDashboard = 'User,ACCESS_TO_DASHBOARD';

    case RoleUserAdd = 'User,ROLE_USER_ADD';
    case RoleUserDelete = 'User,ROLE_USER_DELETE';
    case RoleUserUpdate = 'User,ROLE_USER_UPDATE';
    case RoleUserList = 'User,ROLE_USER_LIST';
    case RoleUserShow = 'User,ROLE_USER_SHOW';

    case RoleRoleList = 'Role,ROLE_SYSTEM_ROLE_LIST';

    case RoleRoleGroupAdd = 'Role Group,ROLE_SYSTEM_ROLE_GROUP_ADD';
    case RoleRoleGroupDelete = 'Role Group,ROLE_SYSTEM_ROLE_GROUP_DELETE';
    case RoleRoleGroupUpdate = 'Role Group,ROLE_SYSTEM_ROLE_GROUP_UPDATE';
    case RoleRoleGroupList = 'Role Group,ROLE_SYSTEM_ROLE_GROUP_LIST';
    case RoleRoleGroupShow = 'Role Group,ROLE_SYSTEM_ROLE_GROUP_SHOW';

    case RoleCenterAdd = 'Center,ROLE_CENTER_ADD';
    case RoleCenterDelete = 'Center,ROLE_CENTER_DELETE';
    case RoleCenterUpdate = 'Center,ROLE_CENTER_UPDATE';
    case RoleCenterList = 'Center,ROLE_CENTER_LIST';
    case RoleCenterShow = 'Center,ROLE_CENTER_SHOW';
    
    case RoleCarAdd = 'Car,ROLE_CAR_ADD';
    case RoleCarDelete = 'Car,ROLE_CAR_DELETE';
    case RoleCarUpdate = 'Car,ROLE_CAR_UPDATE';
    case RoleCarList = 'Car,ROLE_CAR_LIST';
    case RoleCarShow = 'Car,ROLE_CAR_SHOW';

    case RoleAddressAdd = 'Address,ROLE_ADDRESS_ADD';
    case RoleAddressDelete = 'Address,ROLE_ADDRESS_DELETE';
    case RoleAddressUpdate = 'Address,ROLE_ADDRESS_UPDATE';
    case RoleAddressList = 'Address,ROLE_ADDRESS_LIST';
    case RoleAddressShow = 'Address,ROLE_ADDRESS_SHOW';

    case RoleDeviceAdd = 'Device,ROLE_DEVICE_ADD';
    case RoleDeviceDelete = 'Device,ROLE_DEVICE_DELETE';
    case RoleDeviceUpdate = 'Device,ROLE_DEVICE_UPDATE';
    case RoleDeviceList = 'Device,ROLE_DEVICE_LIST';
    case RoleDeviceShow = 'Device,ROLE_DEVICE_SHOW';

    case RoleForestAdd = 'Forest,ROLE_FOREST_ADD';
    case RoleForestDelete = 'Forest,ROLE_FOREST_DELETE';
    case RoleForestUpdate = 'Forest,ROLE_FOREST_UPDATE';
    case RoleForestList = 'Forest,ROLE_FOREST_LIST';
    case RoleForestShow = 'Forest,ROLE_FOREST_SHOW';

    case RoleFireAdd = 'Fire,ROLE_FIRE_ADD';
    case RoleFireDelete = 'Fire,ROLE_FIRE_DELETE';
    case RoleFireUpdate = 'Fire,ROLE_FIRE_UPDATE';
    case RoleFireList = 'Fire,ROLE_FIRE_LIST';
    case RoleFireShow = 'Fire,ROLE_FIRE_SHOW';

    case RoleFireBrigadeAdd = 'FireBrigade,ROLE_FIRE_BRIGADE_ADD';
    case RoleFireBrigadeDelete = 'FireBrigade,ROLE_FIRE_BRIGADE_DELETE';
    case RoleFireBrigadeUpdate = 'FireBrigade,ROLE_FIRE_BRIGADE_UPDATE';
    case RoleFireBrigadeList = 'FireBrigade,ROLE_FIRE_BRIGADE_LIST';
    case RoleFireBrigadeShow = 'FireBrigade,ROLE_FIRE_BRIGADE_SHOW';

    case RoleDeviceValueAdd = 'DeviceValue,ROLE_DEVICE_VALUE_ADD';
    case RoleDeviceValueDelete = 'DeviceValue,ROLE_DEVICE_VALUE_DELETE';
    case RoleDeviceValueUpdate = 'DeviceValue,ROLE_DEVICE_VALUE_UPDATE';
    case RoleDeviceValueList = 'DeviceValue,ROLE_DEVICE_VALUE_LIST';
    case RoleDeviceValueShow = 'DeviceValue,ROLE_DEVICE_VALUE_SHOW';

    case RoleTaskFireBrigadeAdd = 'TaskFireBrigade,ROLE_TASK_FIRE_BRIGADE_ADD';
    case RoleTaskFireBrigadeDelete = 'TaskFireBrigade,ROLE_TASK_FIRE_BRIGADE_DELETE';
    case RoleTaskFireBrigadeUpdate = 'TaskFireBrigade,ROLE_TASK_FIRE_BRIGADE_UPDATE';
    case RoleTaskFireBrigadeList = 'TaskFireBrigade,ROLE_TASK_FIRE_BRIGADE_LIST';
    case RoleTaskFireBrigadeShow = 'TaskFireBrigade,ROLE_TASK_FIRE_BRIGADE_SHOW';
    
    case RoleEmergencyRequestAdd = 'EmergencyRequest,ROLE_EMERGENCY_REQUEST_ADD';
    case RoleEmergencyRequestDelete = 'EmergencyRequest,ROLE_EMERGENCY_REQUEST_DELETE';
    case RoleEmergencyRequestUpdate = 'EmergencyRequest,ROLE_EMERGENCY_REQUEST_UPDATE';
    case RoleEmergencyRequestList = 'EmergencyRequest,ROLE_EMERGENCY_REQUEST_LIST';
    case RoleEmergencyRequestShow = 'EmergencyRequest,ROLE_EMERGENCY_REQUEST_SHOW';
}
