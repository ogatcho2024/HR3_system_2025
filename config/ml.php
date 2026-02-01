<?php

return [
    'python_bin' => env('ML_PYTHON_BIN', 'python'),
    'scripts_path' => env('ML_SCRIPTS_PATH', base_path('ml')),
    'approval_model_path' => env('ML_APPROVAL_MODEL_PATH', base_path('ml/models/approval_model.pkl')),
    'demand_model_path' => env('ML_DEMAND_MODEL_PATH', base_path('ml/models/demand_model.pkl')),
];
