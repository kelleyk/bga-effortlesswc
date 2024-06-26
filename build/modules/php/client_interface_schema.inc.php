<?php declare(strict_types=1);

const CLIENT_INTERFACE_SCHEMA = <<<'SCHEMA'
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "definitions": {
    "CancelAction": {
      "additionalProperties": false,
      "type": "object"
    },
    "PlaceEffortAction": {
      "additionalProperties": false,
      "type": "object"
    },
    "PlayerActions": {
      "additionalProperties": false,
      "type": "object"
    },
    "SelectionAction": {
      "additionalProperties": false,
      "type": "object"
    },
    "CardsMovedNotif": {
      "additionalProperties": false,
      "type": "object"
    },
    "EffortMovedNotif": {
      "additionalProperties": false,
      "type": "object"
    },
    "EffortPlacedNotif": {
      "additionalProperties": false,
      "type": "object"
    },
    "NotifTypes": {
      "additionalProperties": false,
      "type": "object"
    },
    "Card": {
      "additionalProperties": false,
      "properties": {
        "armorPiece": {
          "type": "string"
        },
        "armorSet": {
          "type": "string"
        },
        "cardType": {
          "type": "string"
        },
        "cardTypeGroup": {
          "type": "string"
        },
        "cardTypeStem": {
          "type": "string"
        },
        "faceDown": {
          "type": "boolean"
        },
        "id": {
          "type": "number"
        },
        "itemNo": {
          "type": "number"
        },
        "location": {
          "type": "string"
        },
        "order": {
          "type": "number"
        },
        "points": {
          "type": "number"
        },
        "stat": {
          "type": "string"
        },
        "sublocation": {
          "type": "string"
        },
        "sublocationIndex": {
          "type": "number"
        }
      },
      "required": [
        "faceDown",
        "id",
        "location",
        "order",
        "sublocation",
        "sublocationIndex"
      ],
      "type": "object"
    },
    "CardBase": {
      "additionalProperties": false,
      "properties": {
        "cardType": {
          "type": "string"
        },
        "cardTypeGroup": {
          "type": "string"
        },
        "id": {
          "type": "number"
        },
        "location": {
          "type": "string"
        },
        "order": {
          "type": "number"
        },
        "sublocation": {
          "type": "string"
        },
        "sublocationIndex": {
          "type": "number"
        }
      },
      "required": [
        "id",
        "location",
        "sublocation",
        "sublocationIndex",
        "order"
      ],
      "type": "object"
    },
    "EffortlessLocation": {
      "additionalProperties": false,
      "properties": {
        "cardType": {
          "type": "string"
        },
        "cardTypeGroup": {
          "type": "string"
        },
        "id": {
          "type": "number"
        },
        "location": {
          "type": "string"
        },
        "order": {
          "type": "number"
        },
        "sublocation": {
          "type": "string"
        },
        "sublocationIndex": {
          "type": "number"
        }
      },
      "required": [
        "id",
        "location",
        "order",
        "sublocation",
        "sublocationIndex"
      ],
      "type": "object"
    },
    "EffortlessSetting": {
      "additionalProperties": false,
      "properties": {
        "cardType": {
          "type": "string"
        },
        "cardTypeGroup": {
          "type": "string"
        },
        "id": {
          "type": "number"
        },
        "location": {
          "type": "string"
        },
        "order": {
          "type": "number"
        },
        "sublocation": {
          "type": "string"
        },
        "sublocationIndex": {
          "type": "number"
        }
      },
      "required": [
        "id",
        "location",
        "order",
        "sublocation",
        "sublocationIndex"
      ],
      "type": "object"
    },
    "GameStates": {
      "additionalProperties": false,
      "type": "object"
    },
    "Gamedatas": {
      "additionalProperties": false,
      "properties": {
        "immutableBoardState": {
          "$ref": "#/definitions/ImmutableBoardState"
        },
        "mutableBoardState": {
          "$ref": "#/definitions/MutableBoardState"
        }
      },
      "required": [
        "mutableBoardState",
        "immutableBoardState"
      ],
      "type": "object"
    },
    "ImmutableBoardState": {
      "additionalProperties": false,
      "properties": {
        "players": {
          "additionalProperties": {
            "$ref": "#/definitions/PlayerPublic"
          },
          "type": "object"
        }
      },
      "required": [
        "players"
      ],
      "type": "object"
    },
    "MutableBoardState": {
      "additionalProperties": false,
      "properties": {
        "cards": {
          "additionalProperties": {
            "$ref": "#/definitions/Card"
          },
          "type": "object"
        },
        "locations": {
          "additionalProperties": {
            "$ref": "#/definitions/EffortlessLocation"
          },
          "type": "object"
        },
        "seats": {
          "additionalProperties": {
            "$ref": "#/definitions/SeatPublic"
          },
          "type": "object"
        },
        "settings": {
          "additionalProperties": {
            "$ref": "#/definitions/EffortlessSetting"
          },
          "type": "object"
        }
      },
      "required": [
        "seats",
        "cards",
        "locations",
        "settings"
      ],
      "type": "object"
    },
    "PlayerPublic": {
      "additionalProperties": false,
      "properties": {
        "color": {
          "type": "string"
        },
        "id": {
          "type": "string"
        },
        "name": {
          "type": "string"
        },
        "no": {
          "type": "number"
        }
      },
      "required": [
        "id",
        "no",
        "name",
        "color"
      ],
      "type": "object"
    },
    "SeatBase": {
      "additionalProperties": false,
      "properties": {
        "id": {
          "type": "number"
        },
        "playerId": {
          "type": "string"
        },
        "seatColor": {
          "type": "string"
        },
        "seatLabel": {
          "type": "string"
        }
      },
      "required": [
        "id",
        "seatColor",
        "seatLabel"
      ],
      "type": "object"
    },
    "SeatPrivate": {
      "additionalProperties": false,
      "properties": {
        "hand": {
          "items": {
            "$ref": "#/definitions/Card"
          },
          "type": "array"
        },
        "id": {
          "type": "number"
        }
      },
      "required": [
        "id",
        "hand"
      ],
      "type": "object"
    },
    "SeatPublic": {
      "additionalProperties": false,
      "properties": {
        "id": {
          "type": "number"
        },
        "playerId": {
          "type": "string"
        },
        "reserveEffort": {
          "type": "number"
        },
        "seatColor": {
          "type": "string"
        },
        "seatLabel": {
          "type": "string"
        }
      },
      "required": [
        "id",
        "reserveEffort",
        "seatColor",
        "seatLabel"
      ],
      "type": "object"
    },
    "TableConfig": {
      "additionalProperties": false,
      "type": "object"
    }
  }
}
SCHEMA
;
