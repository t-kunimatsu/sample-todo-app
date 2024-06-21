import { ColumnType } from "@/types/todo";
import { useDroppable } from "@dnd-kit/core";
import { SortableContext, rectSortingStrategy } from "@dnd-kit/sortable";
import { AddTask } from "@mui/icons-material";
import { Box, Button, Typography } from "@mui/material";
import { FC, useCallback } from "react";
import Card from "./Card";
import CardDialog from "./CardDialog";
import { useTodoBoard } from "./useTodoBoard";

const Column: FC<ColumnType> = (column) => {
  const { id, title, cards, showAddTask = false, showEditTask = false } = column;
  const { setNodeRef } = useDroppable({ id: id });
  const {
    addCard,
    editCard,
    currentColumnId,
    setCurrentColumnId,
    currentCard,
    setCurrentCard,
    dialogOpen,
    setDialogOpen,
    dialogMode,
    setDialogMode,
  } = useTodoBoard();

  const handleDialogOpen = (columnId: string) => {
    setDialogMode("add");
    setCurrentColumnId(columnId);
    setCurrentCard({ id: "", title: "" });
    setDialogOpen(true);
  };

  const handleDialogClose = () => {
    setDialogOpen(false);
  };

  const handleSaveCard = useCallback(
    (title: string) => {
      if (dialogMode === "add") {
        // TODO >> idはAPIの戻り値
        const newCard = { id: `Card${Date.now()}`, title: title };
        addCard(currentColumnId, newCard);
      } else if (dialogMode === "edit") {
        editCard({ id: currentCard.id, title: title });
      }
      handleDialogClose();
    },
    [dialogMode, currentColumnId, currentCard, addCard, editCard, handleDialogClose]
  );

  return (
    <SortableContext id={id} items={cards} strategy={rectSortingStrategy}>
      <Box
        ref={setNodeRef}
        sx={{
          flex: "1",
          background: "rgba(245,247,249,1.00)",
          marginRight: "10px",
          padding: "8px",
          minHeight: "90dvh",
        }}
      >
        <Box
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            padding: "2px",
            mb: 2,
          }}
        >
          <Typography
            variant="h6"
            sx={{
              fontWeight: "800",
              color: "#575757",
            }}
          >
            {title}
          </Typography>
          {showAddTask && (
            <Button
              variant="contained"
              onClick={() => handleDialogOpen(id)}
              sx={{ padding: "3px" }}
            >
              <AddTask />
            </Button>
          )}
        </Box>
        {cards.map((card) => (
          <Box key={card.id} sx={{ display: "flex", alignItems: "center", padding: "4px" }}>
            <Card {...{ ...card, showEditTask }} />
          </Box>
        ))}
      </Box>
      <CardDialog
        open={dialogOpen}
        onClose={handleDialogClose}
        onSave={handleSaveCard}
        initialTitle={currentCard.title}
      />
    </SortableContext>
  );
};

export default Column;
